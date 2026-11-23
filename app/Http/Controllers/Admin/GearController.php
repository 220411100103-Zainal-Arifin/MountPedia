<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReadsFilters;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Master data: Gear & Kategori (daftar). Tambah/ubah/hapus menyusul. */
class GearController extends Controller
{
    use ReadsFilters;

    private const PER_PAGE = 10;

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'kategori' ? 'kategori' : 'gear';

        $filters = [
            'q' => $this->text($request, 'q'),
            'kategori' => $this->id($request, 'kategori'),
            'sewa' => $this->choice($request, 'sewa', ['1', '0']),
        ];
        $like = $this->like($filters['q']);

        $items = null;
        $categories = null;

        if ($tab === 'gear') {
            $items = DB::table('gear_items')
                ->join('gear_categories', 'gear_categories.id', '=', 'gear_items.gear_category_id')
                ->select(
                    'gear_items.id', 'gear_items.name', 'gear_items.slug', 'gear_items.brand',
                    'gear_items.is_rentable', 'gear_categories.name as category_name'
                )
                ->selectSub(
                    DB::table('rental_items')->selectRaw('count(*)')->whereColumn('rental_items.gear_item_id', 'gear_items.id'),
                    'rental_count'
                )
                ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('gear_items.name', 'like', $like)
                    ->orWhere('gear_items.brand', 'like', $like)))
                ->when($filters['kategori'] !== '', fn ($query) => $query->where('gear_items.gear_category_id', $filters['kategori']))
                ->when($filters['sewa'] !== '', fn ($query) => $query->where('gear_items.is_rentable', (int) $filters['sewa']))
                ->orderBy('gear_categories.sort_order')
                ->orderBy('gear_items.name')
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        } else {
            $categories = DB::table('gear_categories')
                ->select('gear_categories.*')
                ->selectSub(
                    DB::table('gear_items')->selectRaw('count(*)')->whereColumn('gear_items.gear_category_id', 'gear_categories.id'),
                    'items_count'
                )
                ->when($filters['q'] !== '', fn ($query) => $query->where('gear_categories.name', 'like', $like))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        }

        return view('admin.gear.index', [
            'tab' => $tab,
            'items' => $items,
            'categories' => $categories,
            'filters' => $filters,
            'hasFilters' => $this->hasFilters($filters, $tab === 'gear' ? ['q', 'kategori', 'sewa'] : ['q']),
            'totals' => ['gear' => DB::table('gear_items')->count(), 'kategori' => DB::table('gear_categories')->count()],
            'categoryOptions' => DB::table('gear_categories')->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
