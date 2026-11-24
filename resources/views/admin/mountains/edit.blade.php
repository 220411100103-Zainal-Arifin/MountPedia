<x-layouts.admin title="Edit Gunung" section="Master Data">
    <x-admin.page-head :text="'Ubah data '.$mountain->name.'.'">
        <a class="btn btn--ghost" href="{{ route('mountains.show', $mountain->slug) }}" target="_blank" rel="noopener">
            <x-admin.icon name="external-link" /> Lihat di website
        </a>
    </x-admin.page-head>

    @if (session('success'))
        <div class="flash flash--success" role="alert">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="flash flash--error" role="alert">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.mountains.update', $mountain->id) }}" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')
        @include('admin.mountains._form')

        <div class="form-actions">
            <button class="btn btn--danger-ghost" type="button" data-delete
                    data-action="{{ route('admin.mountains.destroy', $mountain->id) }}" data-name="{{ $mountain->name }}"
                    data-note="Semua jalur, camp, foto, status pendakian, biaya simaksi, dan rekomendasi gear milik gunung ini ikut terhapus. Tindakan ini tidak bisa dibatalkan.">
                <x-admin.icon name="trash-2" /> Hapus gunung
            </button>
            <span class="form-actions__right">
                <a class="btn btn--ghost" href="{{ route('admin.mountains.index') }}">Batal</a>
                <button class="btn btn--primary" type="submit">Simpan perubahan</button>
            </span>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════════════════
         JALUR PENDAKIAN
    ═══════════════════════════════════════════════════════════════ --}}
    <section class="panel" style="margin-top:2rem">
        <header class="panel__head" style="display:flex;align-items:center;justify-content:space-between">
            <div>
                <h2>Jalur Pendakian</h2>
                <span>{{ $mountain->trails->count() }} jalur terdaftar</span>
            </div>
            <a class="btn btn--primary btn--sm"
               href="{{ route('admin.trails.create', ['gunung' => $mountain->id]) }}">
                <x-admin.icon name="plus" /> Tambah Jalur
            </a>
        </header>

        @if ($mountain->trails->isEmpty())
            <div class="empty">
                <strong>Belum ada jalur</strong>
                Tambahkan jalur pendakian pertama untuk gunung ini.
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Jalur</th>
                            <th>Basecamp</th>
                            <th class="num">Jarak</th>
                            <th class="num">Naik / Turun</th>
                            <th>Tingkat</th>
                            <th>Status</th>
                            <th><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $diffTone   = ['mudah' => 'green', 'sedang' => 'amber', 'sulit' => 'red', 'ekstrem' => 'dark'];
                            $statusTone = ['buka' => 'green', 'tutup' => 'red'];
                            $statusLbl  = ['buka' => 'Dibuka', 'tutup' => 'Ditutup'];
                            $num = fn ($v) => $v !== null ? rtrim(rtrim(number_format((float) $v, 1, ',', '.'), '0'), ',') : '—';
                        @endphp
                        @foreach ($mountain->trails as $trail)
                            <tr>
                                <td class="cell--title">
                                    <div class="cell-title">
                                        <strong>{{ $trail->name }}</strong>
                                        @if ($trail->status_reason)
                                            <small>{{ Str::limit($trail->status_reason, 60) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $trail->basecamp_name ?? '—' }}</td>
                                <td class="num">{{ $num($trail->distance_km) }} km</td>
                                <td class="num">{{ $num($trail->duration_up_hours) }} / {{ $num($trail->duration_down_hours) }} jam</td>
                                <td>
                                    @if ($trail->difficulty)
                                        <x-admin.badge :tone="$diffTone[$trail->difficulty] ?? 'gray'">{{ ucfirst($trail->difficulty) }}</x-admin.badge>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <x-admin.badge :tone="$statusTone[$trail->status] ?? 'gray'">
                                        {{ $statusLbl[$trail->status] ?? $trail->status }}
                                    </x-admin.badge>
                                </td>
                                <td class="cell--actions">
                                    <x-admin.row-actions
                                        :edit="route('admin.trails.edit', $trail->id)"
                                        :delete="route('admin.trails.destroy', $trail->id)"
                                        :name="$trail->name"
                                        note="Camp dan biaya simaksi yang terkait tetap ada. Tindakan ini tidak bisa dibatalkan." />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- ═══════════════════════════════════════════════════════════════
         AREA KEMAH (CAMPSITE)
    ═══════════════════════════════════════════════════════════════ --}}
    <section class="panel" style="margin-top:1.5rem">
        <header class="panel__head" style="display:flex;align-items:center;justify-content:space-between">
            <div>
                <h2>Area Kemah (Campsite)</h2>
                <span>{{ $mountain->campsites->count() }} campsite terdaftar</span>
            </div>
            <a class="btn btn--primary btn--sm"
               href="{{ route('admin.campsites.create', ['gunung' => $mountain->id]) }}">
                <x-admin.icon name="plus" /> Tambah Campsite
            </a>
        </header>

        @if ($mountain->campsites->isEmpty())
            <div class="empty">
                <strong>Belum ada campsite</strong>
                Tambahkan area kemah yang ada di gunung ini.
            </div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Campsite</th>
                            <th>Jalur</th>
                            <th class="num">Elevasi</th>
                            <th class="num">Kapasitas</th>
                            <th>Air</th>
                            <th><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mountain->campsites as $camp)
                            <tr>
                                <td class="cell--title">
                                    <div class="cell-title">
                                        <strong>{{ $camp->name }}</strong>
                                        @if ($camp->notes)
                                            <small>{{ Str::limit($camp->notes, 60) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $camp->trail?->name ?? '— (semua jalur)' }}</td>
                                <td class="num">
                                    {{ $camp->elevation_mdpl ? number_format($camp->elevation_mdpl, 0, ',', '.') . ' mdpl' : '—' }}
                                </td>
                                <td class="num">
                                    {{ $camp->capacity_tents ? $camp->capacity_tents . ' tenda' : '—' }}
                                </td>
                                <td>
                                    @if ($camp->has_water_source)
                                        <x-admin.badge tone="green">Ada</x-admin.badge>
                                    @else
                                        <x-admin.badge tone="gray">Tidak ada</x-admin.badge>
                                    @endif
                                </td>
                                <td class="cell--actions">
                                    <x-admin.row-actions
                                        :edit="route('admin.campsites.edit', $camp->id)"
                                        :delete="route('admin.campsites.destroy', $camp->id)"
                                        :name="$camp->name"
                                        note="Area kemah ini akan dihapus permanen. Tindakan ini tidak bisa dibatalkan." />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

</x-layouts.admin>
