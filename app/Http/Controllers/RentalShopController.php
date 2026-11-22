<?php

namespace App\Http\Controllers;

use App\Models\Mountain;
use App\Models\Region;
use App\Models\RentalShop;
use App\Support\OpeningHours;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RentalShopController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request)
    {
        $q = $request->query('q');
        $province = $request->query('provinsi');
        $mountain = $request->query('gunung');

        
        $filters = [
            'q' => is_string($q) ? mb_substr(trim($q), 0, 80) : '',
            'provinsi' => is_string($province) ? $province : '',
            'gunung' => is_string($mountain) ? $mountain : '',
            'verified' => $request->query('verified') === '1' ? '1' : '',
        ];

        $shops = RentalShop::query()
            ->join('regions', 'regions.id', '=', 'rental_shops.region_id')
            ->select('rental_shops.*', 'regions.province', 'regions.regency')
            ->addSelect([
                
                'items_count' => DB::table('rental_items')
                    ->selectRaw('count(*)')
                    ->whereColumn('rental_items.rental_shop_id', 'rental_shops.id'),
                'min_price' => DB::table('rental_items')
                    ->selectRaw('min(price_per_day_idr)')
                    ->whereColumn('rental_items.rental_shop_id', 'rental_shops.id'),
            ])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $like = '%'.addcslashes($filters['q'], '%_\\').'%';
                $query->where(fn ($w) => $w
                    ->where('rental_shops.name', 'like', $like)
                    ->orWhere('rental_shops.address', 'like', $like)
                    ->orWhere('regions.regency', 'like', $like));
            })
            ->when($filters['provinsi'] !== '', fn ($query) => $query->where('regions.province', $filters['provinsi']))
            ->when($filters['gunung'] !== '', fn ($query) => $query->whereIn(
                'rental_shops.id',
                DB::table('shop_mountains')
                    ->join('mountains', 'mountains.id', '=', 'shop_mountains.mountain_id')
                    ->where('mountains.slug', $filters['gunung'])
                    ->select('shop_mountains.rental_shop_id')
            ))
            ->when($filters['verified'] === '1', fn ($query) => $query->where('rental_shops.is_verified', true))
            ->orderByDesc('rental_shops.is_verified')
            ->orderBy('rental_shops.name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        
        if ($shops->currentPage() > $shops->lastPage()) {
            return redirect()->to($shops->url($shops->lastPage()));
        }

        
        $mountainsByShop = DB::table('shop_mountains')
            ->join('mountains', 'mountains.id', '=', 'shop_mountains.mountain_id')
            ->whereIn('shop_mountains.rental_shop_id', $shops->pluck('id'))
            ->orderByRaw('shop_mountains.distance_km_to_basecamp is null')
            ->orderBy('shop_mountains.distance_km_to_basecamp')
            ->get(['shop_mountains.rental_shop_id', 'mountains.name'])
            ->groupBy('rental_shop_id');

        return view('sewa.index', [
            'shops' => $shops,
            'mountainsByShop' => $mountainsByShop,
            'provinces' => Region::query()
                ->whereIn('id', RentalShop::query()->select('region_id'))
                ->distinct()->orderBy('province')->pluck('province'),
            'mountains' => Mountain::query()
                ->whereIn('id', DB::table('shop_mountains')->select('mountain_id'))
                ->orderBy('name')->get(['name', 'slug']),
            'filters' => $filters,
            'hasFilters' => collect($filters)->contains(fn ($v) => $v !== ''),
        ]);
    }

    
    public function show(string $slug)
    {
        $shop = RentalShop::query()
            ->join('regions', 'regions.id', '=', 'rental_shops.region_id')
            ->select('rental_shops.*', 'regions.province', 'regions.regency')
            ->where('rental_shops.slug', $slug)
            ->firstOrFail();

        
        $waNumber = self::waNumber($shop->whatsapp ?: $shop->phone);
        $waUrl = fn (string $text) => $waNumber
            ? 'https://wa.me/'.$waNumber.'?text='.rawurlencode($text)
            : null;

        $hasCoords = $shop->latitude !== null && $shop->longitude !== null;
        $directionsUrl = match (true) {
            $hasCoords => 'https://www.google.com/maps/dir/?api=1&destination='.$shop->latitude.','.$shop->longitude,
            filled($shop->address) => 'https://www.google.com/maps/dir/?api=1&destination='
                .rawurlencode($shop->name.', '.$shop->address.', '.$shop->regency),
            default => null,
        };

        
        $photos = DB::table('rental_shop_photos')
            ->where('rental_shop_id', $shop->id)
            ->orderBy('sort_order')->orderBy('id')
            ->get(['url', 'caption'])
            ->map(fn ($p) => ['url' => $p->url, 'caption' => (string) $p->caption])
            ->all();
        if ($photos === [] && filled($shop->image_url)) {
            $photos = [['url' => $shop->image_url, 'caption' => '']];
        }

        
        $rows = DB::table('rental_items as ri')
            ->leftJoin('gear_items as gi', 'gi.id', '=', 'ri.gear_item_id')
            ->leftJoin('gear_categories as gc', 'gc.id', '=', 'gi.gear_category_id')
            ->where('ri.rental_shop_id', $shop->id)
            ->orderByRaw('gc.sort_order is null')
            ->orderBy('gc.sort_order')
            ->orderBy('ri.item_name')
            ->get([
                'ri.id', 'ri.item_name', 'ri.price_per_day_idr', 'ri.stock_note', 'ri.notes',
                'gi.slug as gear_slug', 'gi.image_url', 'gc.name as category_name',
            ]);

        $items = $rows->map(function ($r) use ($shop, $waUrl) {
            $category = $r->category_name ?: 'Lainnya';

            return (object) [
                'name' => $r->item_name,
                'category' => $category,
                'category_key' => Str::slug($category),
                'price' => (int) $r->price_per_day_idr,
                'stock_note' => $r->stock_note,
                'notes' => $r->notes,
                'image' => $r->image_url ?: null,
                'href' => $r->gear_slug ? url('/perlengkapan/'.$r->gear_slug) : null,
                'wa_url' => $waUrl('Halo '.$shop->name.', saya melihat '.$r->item_name
                    .' di MountPedia. Apakah masih tersedia untuk disewa? Terima kasih.'),
            ];
        })->all();

        $categories = collect($items)->groupBy('category_key')->map(fn ($g) => (object) [
            'key' => $g->first()->category_key,
            'name' => $g->first()->category,
            'count' => $g->count(),
            'min' => $g->min('price'),
            'max' => $g->max('price'),
        ])->values()->all();

        
        $mountains = DB::table('shop_mountains')
            ->join('mountains', 'mountains.id', '=', 'shop_mountains.mountain_id')
            ->where('shop_mountains.rental_shop_id', $shop->id)
            ->orderByRaw('shop_mountains.distance_km_to_basecamp is null')
            ->orderBy('shop_mountains.distance_km_to_basecamp')
            ->get(['mountains.name', 'mountains.slug', 'shop_mountains.distance_km_to_basecamp as km', 'shop_mountains.note'])
            ->all();

        
        $reviewQuery = fn () => DB::table('rental_shop_reviews')
            ->where('rental_shop_id', $shop->id)
            ->where('is_published', true);

        $distribution = $reviewQuery()->selectRaw('rating, count(*) as total')
            ->groupBy('rating')->pluck('total', 'rating')->all();
        $reviewCount = array_sum($distribution);
        $reviewStats = (object) [
            'count' => $reviewCount,
            'avg' => $reviewCount
                ? array_sum(array_map(fn ($r, $n) => $r * $n, array_keys($distribution), $distribution)) / $reviewCount
                : null,
            'dist' => collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($r) => [$r => (int) ($distribution[$r] ?? 0)])->all(),
        ];

        $reviews = $reviewQuery()->orderByDesc('created_at')->limit(20)->get()
            ->map(fn ($r) => (object) [
                'name' => $r->reviewer_name,
                'rating' => (int) $r->rating,
                'comment' => $r->comment,
                'date' => Carbon::parse($r->visited_on ?? $r->created_at)->locale('id')->translatedFormat('F Y'),
            ])->all();

        $description = trim((string) $shop->description);

        return view('sewa.show', [
            'shop' => $shop,
            'photos' => $photos,
            'items' => $items,
            'categories' => $categories,
            'terms' => array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $shop->terms)))),
            'mountains' => $mountains,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
            'status' => OpeningHours::status($shop->opening_hours),
            'waGeneral' => $waUrl('Halo '.$shop->name.', saya melihat tempat sewa Anda di MountPedia. Saya ingin menanyakan sewa perlengkapan pendakian.'),
            'phoneDigits' => $shop->phone ? preg_replace('/[^\d+]/', '', $shop->phone) : null,
            'directionsUrl' => $directionsUrl,
            'hasCoords' => $hasCoords,
            'description' => $description,
            'metaDescription' => $description !== ''
                ? Str::limit(strip_tags($description), 155)
                : 'Tempat sewa perlengkapan pendakian di '.$shop->regency.', '.$shop->province.'.',
            'updatedLabel' => $shop->updated_at?->locale('id')->translatedFormat('j F Y'),
        ]);
    }

    
    private static function waNumber(?string $raw): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $raw);
        if ($digits === '' || strlen($digits) < 8) {
            return null;
        }

        return match (true) {
            str_starts_with($digits, '62') => $digits,
            str_starts_with($digits, '0') => '62'.substr($digits, 1),
            str_starts_with($digits, '8') => '62'.$digits,
            default => $digits,
        };
    }
}