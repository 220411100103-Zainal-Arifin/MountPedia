<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrailRequest;
use App\Models\Mountain;
use App\Models\Trail;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Master data: Jalur pendakian (tambah, ubah, hapus). Daftarnya ada di tab Jalur pada halaman Gunung. */
class TrailController extends Controller
{
    public function create(Request $request)
    {
        $trail = new Trail;
        $preselected = $request->query('gunung');
        $trail->mountain_id = is_string($preselected) && ctype_digit($preselected) ? (int) $preselected : null;

        return view('admin.trails.create', [
            'trail' => $trail,
            'mountains' => $this->mountainOptions(),
        ]);
    }

    public function store(TrailRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? $this->uniqueSlug((int) $data['mountain_id'], $data['name']);

        $trail = Trail::create($data);

        return redirect()
            ->route('admin.mountains.index', ['tab' => 'jalur'])
            ->with('success', "{$trail->name} berhasil ditambahkan.");
    }

    public function edit(Trail $trail)
    {
        return view('admin.trails.edit', [
            'trail' => $trail,
            'mountains' => $this->mountainOptions(),
        ]);
    }

    public function update(TrailRequest $request, Trail $trail)
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? $trail->slug;

        $trail->update($data);

        return redirect()
            ->route('admin.mountains.index', ['tab' => 'jalur'])
            ->with('success', "Perubahan {$trail->name} berhasil disimpan.");
    }

    public function destroy(Trail $trail)
    {
        $name = $trail->name;

        try {
            // status pendakian jalur ikut terhapus; camp dan simaksi yang terkait tetap ada (tanpa jalur)
            $trail->delete();
        } catch (QueryException $e) {
            return back()->with('error', "{$name} tidak dapat dihapus karena masih dipakai data lain.");
        }

        return back()->with('success', "{$name} telah dihapus.");
    }

    private function mountainOptions()
    {
        return Mountain::query()->orderBy('name')->get(['id', 'name']);
    }

    private function uniqueSlug(int $mountainId, string $name): string
    {
        $base = Str::slug($name) ?: 'jalur';
        $slug = $base;
        $i = 2;

        while (Trail::query()->where('mountain_id', $mountainId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
