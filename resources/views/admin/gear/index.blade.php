<x-layouts.admin title="Gear & Kategori" section="Master Data">
    @php $current = $tab === 'gear' ? $items : $categories; @endphp

    <x-admin.page-head text="Kelola katalog perlengkapan pendakian dan kategorinya.">
        <span class="btn btn--primary is-disabled" title="Tersedia di tahap berikutnya"><x-admin.icon name="plus" /> Tambah {{ $tab === 'gear' ? 'Gear' : 'Kategori' }}</span>
    </x-admin.page-head>

    <x-admin.tabs route="admin.gear.index" :active="$tab"
        :items="['gear' => ['Gear', $totals['gear']], 'kategori' => ['Kategori', $totals['kategori']]]" />

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('admin.gear.index') }}" data-filter-form>
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label class="tb-search">
                <x-admin.icon name="search" />
                <input type="search" name="q" value="{{ $filters['q'] }}" maxlength="80" autocomplete="off"
                       placeholder="{{ $tab === 'gear' ? 'Cari nama atau merek gear' : 'Cari nama kategori' }}" aria-label="Cari">
            </label>

            @if ($tab === 'gear')
                <label class="tb-select">
                    <span class="sr-only">Kategori</span>
                    <select name="kategori" data-autosubmit>
                        <option value="">Semua kategori</option>
                        @foreach ($categoryOptions as $c)
                            <option value="{{ $c->id }}" @selected($filters['kategori'] === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="tb-select">
                    <span class="sr-only">Bisa disewa</span>
                    <select name="sewa" data-autosubmit>
                        <option value="">Semua</option>
                        <option value="1" @selected($filters['sewa'] === '1')>Bisa disewa</option>
                        <option value="0" @selected($filters['sewa'] === '0')>Tidak disewakan</option>
                    </select>
                </label>
            @endif

            @if ($hasFilters)
                <a class="toolbar__reset" href="{{ route('admin.gear.index', ['tab' => $tab]) }}">Hapus filter</a>
            @endif
        </form>

        @if ($current->isEmpty())
            <div class="empty"><strong>Belum ada data yang cocok</strong>Coba kata kunci lain atau hapus filter yang dipakai.</div>
        @else
            <div class="table-wrap">
                @if ($tab === 'gear')
                    <table class="table table--cards">
                        <thead>
                            <tr>
                                <th>Gear</th>
                                <th>Kategori</th>
                                <th>Merek</th>
                                <th class="num">Ada di tempat sewa</th>
                                <th>Disewakan</th>
                                <th><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $g)
                                <tr>
                                    <td class="cell--title">
                                        <div class="cell-main">
                                            <span class="thumb">{{ Str::upper(Str::substr($g->name, 0, 1)) }}</span>
                                            <div class="cell-title"><strong>{{ $g->name }}</strong><small>{{ $g->slug ?? '—' }}</small></div>
                                        </div>
                                    </td>
                                    <td data-label="Kategori">{{ $g->category_name }}</td>
                                    <td data-label="Merek">{{ $g->brand ?? '—' }}</td>
                                    <td class="num" data-label="Ada di tempat sewa">{{ $g->rental_count }}</td>
                                    <td data-label="Disewakan"><x-admin.badge :tone="$g->is_rentable ? 'green' : 'gray'">{{ $g->is_rentable ? 'Ya' : 'Tidak' }}</x-admin.badge></td>
                                    <td class="cell--actions"><x-admin.row-actions :view="$g->slug ? route('gear.show', $g->slug) : null" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="table table--cards">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="num">Urutan</th>
                                <th class="num">Jumlah gear</th>
                                <th><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $c)
                                <tr>
                                    <td class="cell--title strong">{{ $c->name }}</td>
                                    <td class="num" data-label="Urutan">{{ $c->sort_order }}</td>
                                    <td class="num" data-label="Jumlah gear">{{ $c->items_count }}</td>
                                    <td class="cell--actions"><x-admin.row-actions /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @endif

        <x-admin.pagination :paginator="$current" :label="$tab === 'gear' ? 'gear' : 'kategori'" />
    </section>
</x-layouts.admin>