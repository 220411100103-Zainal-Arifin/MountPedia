@php
    $statusTone = ['buka' => 'green', 'dibatasi' => 'amber', 'tutup' => 'red'];
    $statusLabel = ['buka' => 'Dibuka', 'dibatasi' => 'Dibatasi', 'tutup' => 'Ditutup'];
    $diffTone = ['mudah' => 'green', 'sedang' => 'amber', 'sulit' => 'red', 'ekstrem' => 'dark'];
    $current = $tab === 'gunung' ? $mountains : $trails;
    $num = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, ',', '.'), '0'), ',');
@endphp
<x-layouts.admin title="Gunung & Jalur" section="Master Data">
    <x-admin.page-head text="Kelola data gunung beserta jalur pendakiannya.">
        <span class="btn btn--primary is-disabled" title="Tersedia di tahap berikutnya"><x-admin.icon name="plus" /> Tambah {{ $tab === 'gunung' ? 'Gunung' : 'Jalur' }}</span>
    </x-admin.page-head>

    <x-admin.tabs route="admin.mountains.index" :active="$tab"
        :items="['gunung' => ['Gunung', $totals['gunung']], 'jalur' => ['Jalur', $totals['jalur']]]" />

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('admin.mountains.index') }}" data-filter-form>
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="tb-search">
                <x-admin.icon name="search" />
                <input type="search" name="q" value="{{ $filters['q'] }}" maxlength="80" autocomplete="off"
                       placeholder="{{ $tab === 'gunung' ? 'Cari nama gunung atau kabupaten' : 'Cari jalur, basecamp, atau gunung' }}"
                       aria-label="Cari">
            </label>

            @if ($tab === 'gunung')
                <label class="tb-select">
                    <span class="sr-only">Provinsi</span>
                    <select name="provinsi" data-autosubmit>
                        <option value="">Semua provinsi</option>
                        @foreach ($provinces as $province)
                            <option value="{{ $province }}" @selected($filters['provinsi'] === $province)>{{ $province }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="tb-select">
                    <span class="sr-only">Status pendakian</span>
                    <select name="status" data-autosubmit>
                        <option value="">Semua status</option>
                        @foreach ($statusLabel as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <label class="tb-select">
                    <span class="sr-only">Tingkat kesulitan</span>
                    <select name="tingkat" data-autosubmit>
                        <option value="">Semua tingkat</option>
                        @foreach (['mudah', 'sedang', 'sulit', 'ekstrem'] as $d)
                            <option value="{{ $d }}" @selected($filters['tingkat'] === $d)>{{ ucfirst($d) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="tb-select">
                    <span class="sr-only">Status jalur</span>
                    <select name="aktif" data-autosubmit>
                        <option value="">Aktif &amp; nonaktif</option>
                        <option value="1" @selected($filters['aktif'] === '1')>Aktif</option>
                        <option value="0" @selected($filters['aktif'] === '0')>Nonaktif</option>
                    </select>
                </label>
            @endif

            @if ($hasFilters)
                <a class="toolbar__reset" href="{{ route('admin.mountains.index', ['tab' => $tab]) }}">Hapus filter</a>
            @endif
        </form>

        @if ($current->isEmpty())
            <div class="empty"><strong>Belum ada data yang cocok</strong>Coba kata kunci lain atau hapus filter yang dipakai.</div>
        @else
            <div class="table-wrap">
                @if ($tab === 'gunung')
                    <table class="table table--cards">
                        <thead>
                            <tr>
                                <th>Gunung</th>
                                <th>Wilayah</th>
                                <th class="num">Ketinggian</th>
                                <th class="num">Jalur</th>
                                <th>Status</th>
                                <th><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mountains as $m)
                                <tr>
                                    <td class="cell--title">
                                        <div class="cell-main">
                                            <span class="thumb">{{ Str::upper(Str::substr(Str::replaceFirst('Gunung ', '', $m->name), 0, 1)) }}</span>
                                            <div class="cell-title"><strong>{{ $m->name }}</strong><small>{{ $m->slug }}</small></div>
                                        </div>
                                    </td>
                                    <td data-label="Wilayah">
                                        @if ($m->region)
                                            {{ Str::replaceFirst('Kabupaten ', '', $m->region->regency) }}, {{ $m->region->province }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="num" data-label="Ketinggian">{{ number_format($m->elevation_mdpl, 0, ',', '.') }} mdpl</td>
                                    <td class="num" data-label="Jalur">{{ $m->trails_count }}</td>
                                    <td data-label="Status">
                                        <x-admin.badge :tone="$statusTone[$m->current_status] ?? 'gray'">
                                            {{ $statusLabel[$m->current_status] ?? 'Belum ada' }}
                                        </x-admin.badge>
                                    </td>
                                    <td class="cell--actions"><x-admin.row-actions :view="route('mountains.show', $m->slug)" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="table table--cards">
                        <thead>
                            <tr>
                                <th>Jalur</th>
                                <th>Rute</th>
                                <th>Tingkat</th>
                                <th>Status</th>
                                <th><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trails as $t)
                                <tr>
                                    <td class="cell--title">
                                        <div class="cell-title"><strong>{{ $t->name }}</strong><small>{{ $t->mountain_name }}</small></div>
                                    </td>
                                    <td data-label="Rute">
                                        <div class="cell-title">
                                            <strong>{{ $t->basecamp_name ?? '—' }}</strong>
                                            <small>
                                                {{ $t->distance_km !== null ? $num($t->distance_km).' km' : '— km' }}
                                                ·
                                                {{ $t->duration_up_hours !== null ? $num($t->duration_up_hours).' / '.$num($t->duration_down_hours ?? 0).' jam' : '— jam' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td data-label="Tingkat">
                                        @if ($t->difficulty)
                                            <x-admin.badge :tone="$diffTone[$t->difficulty] ?? 'gray'">{{ ucfirst($t->difficulty) }}</x-admin.badge>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td data-label="Status"><x-admin.badge :tone="$t->is_active ? 'green' : 'gray'">{{ $t->is_active ? 'Aktif' : 'Nonaktif' }}</x-admin.badge></td>
                                    <td class="cell--actions"><x-admin.row-actions :view="route('mountains.show', $t->mountain_slug)" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        <x-admin.pagination :paginator="$current" :label="$tab === 'gunung' ? 'gunung' : 'jalur'" />
    </section>
</x-layouts.admin>