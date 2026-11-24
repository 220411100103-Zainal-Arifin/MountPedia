<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReadsFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MountainRequest;
use App\Models\Mountain;
use App\Models\Region;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Master data: Gunung (daftar, tambah, ubah, hapus). Jalur ada di TrailController. */
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
            'status' => $this->choice($request, 'status', ['buka', 'tutup']),
            'tingkat' => $this->choice($request, 'tingkat', ['mudah', 'sedang', 'sulit', 'ekstrem']),
            'aktif' => $this->choice($request, 'aktif', ['1', '0']),
        ];
        $like = $this->like($filters['q']);

        $mountains = null;
        $trails = null;

        if ($tab === 'gunung') {
            $mountains = Mountain::query()
                ->with('region')
                ->withCount('trails')
                ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($w) => $w
                    ->where('name', 'like', $like)
                    ->orWhereIn('region_id', Region::query()->select('id')->where('regency', 'like', $like))))
                ->when($filters['provinsi'] !== '', fn ($query) => $query
                    ->whereIn('region_id', Region::query()->select('id')->where('province', $filters['provinsi'])))
                ->when($filters['status'] !== '', fn ($query) => $query
                    ->where('status', $filters['status']))
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

        $page = $tab === 'gunung' ? $mountains : $trails;
        if ($page->currentPage() > $page->lastPage()) {
            return redirect()->to($page->url($page->lastPage()));
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

    public function create()
    {
        return view('admin.mountains.create', [
            'mountain' => new Mountain,
            'regions' => $this->regionOptions(),
        ]);
    }

    public function store(MountainRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? $this->uniqueSlug($data['name']);

        $mountain = Mountain::create($data);

        return redirect()
            ->route('admin.mountains.index')
            ->with('success', "{$mountain->name} berhasil ditambahkan.");
    }

    public function edit(Mountain $mountain)
    {
        return view('admin.mountains.edit', [
            'mountain' => $mountain,
            'regions' => $this->regionOptions(),
        ]);
    }

    public function update(MountainRequest $request, Mountain $mountain)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? $mountain->slug;

        $mountain->update($data);

        return redirect()
            ->route('admin.mountains.index')
            ->with('success', "Perubahan {$mountain->name} berhasil disimpan.");
    }

    public function destroy(Mountain $mountain)
    {
        $name = $mountain->name;

        try {
            // jalur, camp, foto, status, simaksi, dan rekomendasi gear ikut terhapus (ON DELETE CASCADE)
            $mountain->delete();
        } catch (QueryException $e) {
            return back()->with('error', "{$name} tidak dapat dihapus karena masih dipakai data lain.");
        }

        return back()->with('success', "{$name} telah dihapus.");
    }

    /** Wilayah dikelompokkan per provinsi untuk dropdown. */
    private function regionOptions()
    {
        return Region::query()->orderBy('province')->orderBy('regency')->get()->groupBy('province');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'gunung';
        $slug = $base;
        $i = 2;

        while (Mountain::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
