<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReadsFilters;
use App\Http\Controllers\Controller;
use App\Models\Mountain;
use App\Models\Region;
use App\Models\TrailStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Master data: Gunung & Jalur (daftar). Tambah/ubah/hapus menyusul. */
class MountainController extends Controller
{
    use ReadsFilters;

    private const PER_PAGE = 10;

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'jalur' ? 'jalur' : 'gunung';

        $filters = [
            'q' => $this->text($request, 'q'),
            'provinsi' => $this->text($request, 'provinsi', 100),
            'status' => $this->choice($request, 'status', ['buka', 'dibatasi', 'tutup']),
            'tingkat' => $this->choice($request, 'tingkat', ['mudah', 'sedang', 'sulit', 'ekstrem']),
            'aktif' => $this->choice($request, 'aktif', ['1', '0']),
        ];
        $like = $this->like($filters['q']);

        $mountains = null;
        $trails = null;

        if ($tab === 'gunung') {
            // status terkini tingkat gunung, sama seperti di halaman publik
            $currentStatus = fn () => TrailStatusLog::query()
                ->select('status')
                ->whereColumn((new TrailStatusLog)->qualifyColumn('mountain_id'), (new Mountain)->qualifyColumn('id'))
                ->mountainLevel()
                ->activeToday()
                ->orderByDesc('reported_at')
                ->limit(1);

            $mountains = Mountain::query()
                ->with('region')
                ->withCount('trails')
                ->addSelect(['current_status' => $currentStatus()])
                ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('name', 'like', $like)
                    ->orWhereIn('region_id', Region::query()->select('id')->where('regency', 'like', $like))))
                ->when($filters['provinsi'] !== '', fn ($query) => $query
                    ->whereIn('region_id', Region::query()->select('id')->where('province', $filters['provinsi'])))
                ->when($filters['status'] !== '', fn ($query) => $query
                    ->where($currentStatus(), '=', $filters['status']))
                ->orderBy('name')
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        } else {
            $trails = DB::table('trails')
                ->join('mountains', 'mountains.id', '=', 'trails.mountain_id')
                ->select('trails.*', 'mountains.name as mountain_name', 'mountains.slug as mountain_slug')
                ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('trails.name', 'like', $like)
                    ->orWhere('trails.basecamp_name', 'like', $like)
                    ->orWhere('mountains.name', 'like', $like)))
                ->when($filters['tingkat'] !== '', fn ($query) => $query->where('trails.difficulty', $filters['tingkat']))
                ->when($filters['aktif'] !== '', fn ($query) => $query->where('trails.is_active', (int) $filters['aktif']))
                ->orderBy('mountains.name')
                ->orderBy('trails.name')
                ->paginate(self::PER_PAGE)
                ->withQueryString();
        }

        return view('admin.mountains.index', [
            'tab' => $tab,
            'mountains' => $mountains,
            'trails' => $trails,
            'filters' => $filters,
            'hasFilters' => $this->hasFilters($filters, $tab === 'gunung' ? ['q', 'provinsi', 'status'] : ['q', 'tingkat', 'aktif']),
            'totals' => ['gunung' => DB::table('mountains')->count(), 'jalur' => DB::table('trails')->count()],
            'provinces' => Region::query()
                ->whereIn('id', Mountain::query()->select('region_id'))
                ->distinct()->orderBy('province')->pluck('province'),
        ]);
    }
}
