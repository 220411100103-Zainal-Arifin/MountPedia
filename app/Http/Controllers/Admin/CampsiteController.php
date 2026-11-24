<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CampsiteRequest;
use App\Models\Campsite;
use App\Models\Mountain;
use App\Models\Trail;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

/** Master data: Campsite / area kemah (tambah, ubah, hapus). Daftarnya ada di halaman edit gunung. */
class CampsiteController extends Controller
{
    public function create(Request $request)
    {
        $campsite = new Campsite;

        // Pra-isi mountain & trail dari query string (tombol "+ Tambah Camp" di halaman edit gunung)
        $mountainId = is_string($request->query('gunung')) && ctype_digit($request->query('gunung'))
            ? (int) $request->query('gunung') : null;
        $trailId = is_string($request->query('jalur')) && ctype_digit($request->query('jalur'))
            ? (int) $request->query('jalur') : null;

        $campsite->mountain_id = $mountainId;
        $campsite->trail_id    = $trailId;

        return view('admin.campsites.create', [
            'campsite' => $campsite,
            'mountains' => $this->mountainOptions(),
            'trails'    => $mountainId ? $this->trailOptions($mountainId) : collect(),
            'backUrl'   => $mountainId
                ? route('admin.mountains.edit', $mountainId)
                : route('admin.mountains.index'),
        ]);
    }

    public function store(CampsiteRequest $request)
    {
        $campsite = Campsite::create($request->validated());

        return redirect()
            ->route('admin.mountains.edit', $campsite->mountain_id)
            ->with('success', "{$campsite->name} berhasil ditambahkan.");
    }

    public function edit(Campsite $campsite)
    {
        return view('admin.campsites.edit', [
            'campsite' => $campsite,
            'mountains' => $this->mountainOptions(),
            'trails'    => $this->trailOptions($campsite->mountain_id),
        ]);
    }

    public function update(CampsiteRequest $request, Campsite $campsite)
    {
        $campsite->update($request->validated());

        return redirect()
            ->route('admin.mountains.edit', $campsite->mountain_id)
            ->with('success', "Perubahan {$campsite->name} berhasil disimpan.");
    }

    public function destroy(Campsite $campsite)
    {
        $name       = $campsite->name;
        $mountainId = $campsite->mountain_id;

        try {
            $campsite->delete();
        } catch (QueryException) {
            return back()->with('error', "{$name} tidak dapat dihapus karena masih dipakai data lain.");
        }

        return redirect()
            ->route('admin.mountains.edit', $mountainId)
            ->with('success', "{$name} telah dihapus.");
    }

    // ─── helpers ──────────────────────────────────────────────────────────────

    private function mountainOptions()
    {
        return Mountain::query()->orderBy('name')->get(['id', 'name']);
    }

    private function trailOptions(int $mountainId)
    {
        return Trail::query()
            ->where('mountain_id', $mountainId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
