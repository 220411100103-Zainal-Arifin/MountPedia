<?php

namespace App\Http\Controllers;

use App\Models\GearCategory;
use App\Models\GearItem;
use App\Models\GearRecommendation;
use App\Models\TripType;
use Illuminate\Http\Request;

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
}
