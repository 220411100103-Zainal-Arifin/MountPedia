<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReadsFilters;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Master data: Barang Sewa (daftar). Tambah/ubah/hapus menyusul. */
class RentalItemController extends Controller
{
    use ReadsFilters;

    private const PER_PAGE = 10;

    public function index(Request $request)
    {
        $filters = [
            'q' => $this->text($request, 'q'),
            'toko' => $this->id($request, 'toko'),
            'kategori' => $this->id($request, 'kategori'),
        ];
        $like = $this->like($filters['q']);

        $items = DB::table('rental_items')
            ->join('rental_shops', 'rental_shops.id', '=', 'rental_items.rental_shop_id')
            ->leftJoin('gear_items', 'gear_items.id', '=', 'rental_items.gear_item_id')
            ->leftJoin('gear_categories', 'gear_categories.id', '=', 'gear_items.gear_category_id')
            ->select(
                'rental_items.*',
                'rental_shops.name as shop_name', 'rental_shops.slug as shop_slug',
                'gear_items.name as gear_name', 'gear_categories.name as category_name'
            )
            ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('rental_items.item_name', 'like', $like)
                ->orWhere('rental_shops.name', 'like', $like)))
            ->when($filters['toko'] !== '', fn ($query) => $query->where('rental_items.rental_shop_id', $filters['toko']))
            ->when($filters['kategori'] !== '', fn ($query) => $query->where('gear_items.gear_category_id', $filters['kategori']))
            ->orderBy('rental_shops.name')
            ->orderBy('rental_items.item_name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.items.index', [
            'items' => $items,
            'filters' => $filters,
            'hasFilters' => $this->hasFilters($filters, ['q', 'toko', 'kategori']),
            'total' => DB::table('rental_items')->count(),
            'shopOptions' => DB::table('rental_shops')->orderBy('name')->get(['id', 'name']),
            'categoryOptions' => DB::table('gear_categories')->orderBy('sort_order')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
