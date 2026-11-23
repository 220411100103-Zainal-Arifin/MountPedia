<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReadsFilters;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Master data: Tempat Sewa (daftar). Tambah/ubah/hapus menyusul. */
class RentalShopController extends Controller
{
    use ReadsFilters;

    private const PER_PAGE = 10;

    public function index(Request $request)
    {
        $filters = [
            'q' => $this->text($request, 'q'),
            'provinsi' => $this->text($request, 'provinsi', 100),
            'verifikasi' => $this->choice($request, 'verifikasi', ['1', '0']),
        ];
        $like = $this->like($filters['q']);

        $shops = DB::table('rental_shops')
            ->join('regions', 'regions.id', '=', 'rental_shops.region_id')
            ->select(
                'rental_shops.id', 'rental_shops.name', 'rental_shops.slug', 'rental_shops.phone',
                'rental_shops.whatsapp', 'rental_shops.opening_hours', 'rental_shops.is_verified',
                'regions.regency', 'regions.province'
            )
            ->selectSub(
                DB::table('rental_items')->selectRaw('count(*)')->whereColumn('rental_items.rental_shop_id', 'rental_shops.id'),
                'items_count'
            )
            ->selectSub(
                DB::table('rental_shop_reviews')->selectRaw('avg(rating)')
                    ->whereColumn('rental_shop_reviews.rental_shop_id', 'rental_shops.id')->where('is_published', 1),
                'rating_avg'
            )
            ->selectSub(
                DB::table('rental_shop_reviews')->selectRaw('count(*)')
                    ->whereColumn('rental_shop_reviews.rental_shop_id', 'rental_shops.id')->where('is_published', 1),
                'reviews_count'
            )
            ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('rental_shops.name', 'like', $like)
                ->orWhere('regions.regency', 'like', $like)))
            ->when($filters['provinsi'] !== '', fn ($query) => $query->where('regions.province', $filters['provinsi']))
            ->when($filters['verifikasi'] !== '', fn ($query) => $query->where('rental_shops.is_verified', (int) $filters['verifikasi']))
            ->orderBy('rental_shops.name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('admin.shops.index', [
            'shops' => $shops,
            'filters' => $filters,
            'hasFilters' => $this->hasFilters($filters, ['q', 'provinsi', 'verifikasi']),
            'total' => DB::table('rental_shops')->count(),
            'unverified' => DB::table('rental_shops')->where('is_verified', 0)->count(),
            'provinces' => DB::table('regions')
                ->whereIn('id', DB::table('rental_shops')->select('region_id'))
                ->distinct()->orderBy('province')->pluck('province'),
        ]);
    }
}
