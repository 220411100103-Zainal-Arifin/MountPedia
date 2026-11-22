<?php

namespace App\Http\Controllers;

use App\Models\GearCategory;
use App\Models\GearItem;
use App\Models\GearRecommendation;
use App\Models\TripType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GearController extends Controller
{
    private const PER_PAGE = 12;

    private const TRIPS = ['tektok', 'camping'];

    public function index(Request $request)
    {
        $q = $request->query('q');
        $category = $request->query('kategori');
        $trip = $request->query('jenis');

        // Nilai yang tidak valid diabaikan, bukan menyebabkan error
        $filters = [
            'q' => is_string($q) ? mb_substr(trim($q), 0, 80) : '',
            'kategori' => is_string($category) && ctype_digit($category) ? $category : '',
            'jenis' => is_string($trip) && in_array($trip, self::TRIPS, true) ? $trip : '',
            'sewa' => $request->query('sewa') === '1' ? '1' : '',
        ];

        $tripType = $filters['jenis'] !== '' ? TripType::where('code', $filters['jenis'])->first() : null;

        $items = GearItem::query()
            ->join('gear_categories', 'gear_categories.id', '=', 'gear_items.gear_category_id')
            ->select('gear_items.*', 'gear_categories.name as category_name', 'gear_categories.id as category_id')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $like = '%'.addcslashes($filters['q'], '%_\\').'%';
                $query->where('gear_items.name', 'like', $like);
            })
            ->when($filters['kategori'] !== '', fn ($query) => $query
                ->where('gear_items.gear_category_id', (int) $filters['kategori']))
            ->when($filters['sewa'] === '1', fn ($query) => $query->where('gear_items.is_rentable', true))
            ->when($tripType, function ($query) use ($tripType) {
                $general = fn () => GearRecommendation::query()
                    ->where('trip_type_id', $tripType->id)
                    ->whereNull('mountain_id');

                // hanya perlengkapan yang direkomendasikan untuk jenis pendakian ini,
                // beserta prioritas tertingginya (wajib > disarankan > opsional)
                $query
                    ->whereIn('gear_items.id', $general()->select('gear_item_id'))
                    ->addSelect(['priority' => $general()
                        ->select('priority')
                        ->whereColumn('gear_item_id', 'gear_items.id')
                        ->orderByRaw("case priority when 'wajib' then 0 when 'disarankan' then 1 else 2 end")
                        ->limit(1)]);
            })
            ->orderBy('gear_categories.sort_order')
            ->orderBy('gear_items.name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        // ?page= melebihi halaman terakhir: arahkan ke halaman terakhir
        if ($items->currentPage() > $items->lastPage()) {
            return redirect()->to($items->url($items->lastPage()));
        }

        return view('perlengkapan.index', [
            'items' => $items,
            'categories' => GearCategory::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
            'tripType' => $tripType,
            'hasFilters' => collect($filters)->contains(fn ($v) => $v !== ''),
        ]);
    }

    /** Halaman detail satu barang: /perlengkapan/{slug} */
    public function show(string $slug)
    {
        $item = DB::table('gear_items as gi')
            ->join('gear_categories as gc', 'gc.id', '=', 'gi.gear_category_id')
            ->where('gi.slug', $slug)
            ->select('gi.*', 'gc.name as category_name')
            ->first();
        abort_if($item === null, 404);

        // ---- foto ---------------------------------------------------------
        $photos = DB::table('gear_item_photos')
            ->where('gear_item_id', $item->id)
            ->orderBy('sort_order')->orderBy('id')
            ->get(['url', 'caption'])
            ->map(fn ($p) => ['url' => $p->url, 'caption' => (string) $p->caption])
            ->all();
        if ($photos === [] && filled($item->image_url)) {
            $photos = [['url' => $item->image_url, 'caption' => '']];
        }

        // ---- spesifikasi: merek lebih dulu, lalu pasangan label => nilai ----
        $specs = [];
        if (filled($item->brand)) {
            $specs[] = ['label' => 'Merek', 'value' => $item->brand];
        }
        $decoded = is_string($item->specs) ? json_decode($item->specs, true) : null;
        foreach (is_array($decoded) ? $decoded : [] as $label => $value) {
            if (is_string($label) && is_scalar($value) && trim((string) $value) !== '') {
                $specs[] = ['label' => $label, 'value' => (string) $value];
            }
        }

        // ---- rekomendasi penggunaan (aturan umum, bukan khusus satu gunung) ----
        $priorityLabels = ['wajib' => 'Wajib', 'disarankan' => 'Disarankan', 'opsional' => 'Opsional'];
        $usage = DB::table('gear_recommendations as gr')
            ->join('trip_types as tt', 'tt.id', '=', 'gr.trip_type_id')
            ->where('gr.gear_item_id', $item->id)
            ->whereNull('gr.mountain_id')
            ->orderBy('tt.id')->orderBy('gr.min_nights')
            ->get(['tt.name as trip', 'gr.priority', 'gr.min_nights', 'gr.max_nights', 'gr.quantity_note', 'gr.notes'])
            ->map(fn ($r) => (object) [
                'trip' => $r->trip,
                'priority' => $r->priority,
                'priority_label' => $priorityLabels[$r->priority] ?? $r->priority,
                'nights' => match (true) {
                    $r->max_nights !== null && (int) $r->max_nights === (int) $r->min_nights => $r->min_nights.' malam',
                    $r->max_nights !== null => $r->min_nights.'–'.$r->max_nights.' malam',
                    (int) $r->min_nights > 0 => 'mulai '.$r->min_nights.' malam',
                    default => null,
                },
                'note' => collect([$r->quantity_note, $r->notes])->filter()->implode('. '),
            ])->all();

        // ---- tempat sewa yang menyediakan barang ini ------------------------
        $shops = DB::table('rental_items as ri')
            ->join('rental_shops as rs', 'rs.id', '=', 'ri.rental_shop_id')
            ->join('regions as r', 'r.id', '=', 'rs.region_id')
            ->where('ri.gear_item_id', $item->id)
            ->get([
                'ri.item_name', 'ri.price_per_day_idr', 'ri.stock_note', 'ri.notes',
                'rs.name', 'rs.slug', 'rs.address', 'rs.image_url', 'rs.is_verified',
                'r.regency', 'r.province',
            ])
            ->groupBy('slug')
            ->map(fn ($rows) => $rows->sortBy('price_per_day_idr')->first()) // satu baris per toko: termurah
            ->sortBy([['price_per_day_idr', 'asc'], ['name', 'asc']])
            ->values();

        $minPrice = $shops->min('price_per_day_idr');
        $maxPrice = $shops->max('price_per_day_idr');
        $shops = $shops->map(fn ($s) => (object) [
            'name' => $s->name,
            'url' => url('/sewa/'.$s->slug),
            'location' => $s->regency.', '.$s->province,
            'address' => $s->address,
            'image' => $s->image_url ?: null,
            'verified' => (bool) $s->is_verified,
            'listed_as' => $s->item_name !== $item->name ? $s->item_name : null,
            'price' => (int) $s->price_per_day_idr,
            'stock_note' => $s->stock_note,
            'notes' => $s->notes,
            'cheapest' => $shops->count() > 1 && $minPrice < $maxPrice && (int) $s->price_per_day_idr === (int) $minPrice,
        ])->all();

        // ---- perlengkapan serupa (kategori sama) -----------------------------
        $related = DB::table('gear_items as gi')
            ->where('gi.gear_category_id', $item->gear_category_id)
            ->where('gi.id', '!=', $item->id)
            ->whereNotNull('gi.slug')
            ->select('gi.name', 'gi.slug', 'gi.image_url')
            ->addSelect(['min_price' => DB::table('rental_items')
                ->selectRaw('min(price_per_day_idr)')
                ->whereColumn('rental_items.gear_item_id', 'gi.id')])
            ->orderBy('gi.name')
            ->limit(10)
            ->get()->all();

        $lines = fn (?string $text) => array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $text))));
        $body = trim((string) $item->body);
        $short = trim((string) $item->description);

        return view('perlengkapan.show', [
            'item' => $item,
            'photos' => $photos,
            'short' => $short,
            'paragraphs' => $body !== '' ? array_values(array_filter(preg_split('/\R{2,}/u', $body))) : [],
            'features' => $lines($item->features),
            'usageNotes' => trim((string) $item->usage_notes),
            'usage' => $usage,
            'specs' => $specs,
            'shops' => $shops,
            'minPrice' => $minPrice,
            'related' => $related,
            'categoryUrl' => route('gear.index', ['kategori' => $item->gear_category_id]),
            'metaDescription' => $short !== '' ? Str::limit(strip_tags($short), 155)
                : $item->name.': informasi, spesifikasi, dan tempat sewa perlengkapan pendakian.',
        ]);
    }
}