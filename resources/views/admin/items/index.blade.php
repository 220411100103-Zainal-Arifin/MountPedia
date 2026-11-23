<x-layouts.admin title="Barang Sewa" section="Master Data">
    <x-admin.page-head text="Kelola daftar barang yang disewakan di setiap tempat sewa beserta harganya.">
        <span class="btn btn--primary is-disabled" title="Tersedia di tahap berikutnya"><x-admin.icon name="plus" /> Tambah Barang Sewa</span>
    </x-admin.page-head>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('admin.items.index') }}" data-filter-form>
            <label class="tb-search">
                <x-admin.icon name="search" />
                <input type="search" name="q" value="{{ $filters['q'] }}" maxlength="80" autocomplete="off"
                       placeholder="Cari nama barang atau tempat sewa" aria-label="Cari">
            </label>
            <label class="tb-select">
                <span class="sr-only">Tempat sewa</span>
                <select name="toko" data-autosubmit>
                    <option value="">Semua tempat sewa</option>
                    @foreach ($shopOptions as $s)
                        <option value="{{ $s->id }}" @selected($filters['toko'] === (string) $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="tb-select">
                <span class="sr-only">Kategori</span>
                <select name="kategori" data-autosubmit>
                    <option value="">Semua kategori</option>
                    @foreach ($categoryOptions as $c)
                        <option value="{{ $c->id }}" @selected($filters['kategori'] === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </label>
            @if ($hasFilters)
                <a class="toolbar__reset" href="{{ route('admin.items.index') }}">Hapus filter</a>
            @endif
        </form>

        @if ($items->isEmpty())
            <div class="empty"><strong>Belum ada data yang cocok</strong>Coba kata kunci lain atau hapus filter yang dipakai.</div>
        @else
            <div class="table-wrap">
                <table class="table table--cards">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Tempat sewa</th>
                            <th>Kategori</th>
                            <th class="num">Harga / hari</th>
                            <th>Stok</th>
                            <th><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $i)
                            <tr>
                                <td class="cell--title">
                                    <div class="cell-title">
                                        <strong>{{ $i->item_name }}</strong>
                                        <small>{{ $i->gear_name ? 'Katalog: '.$i->gear_name : 'Belum terhubung ke katalog gear' }}</small>
                                    </div>
                                </td>
                                <td data-label="Tempat sewa">{{ $i->shop_name }}</td>
                                <td data-label="Kategori">{{ $i->category_name ?? '—' }}</td>
                                <td class="num strong" data-label="Harga / hari">Rp {{ number_format($i->price_per_day_idr, 0, ',', '.') }}</td>
                                <td class="cell-clip" data-label="Stok">{{ $i->stock_note ?? '—' }}</td>
                                <td class="cell--actions"><x-admin.row-actions :view="route('rental.show', $i->shop_slug)" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <x-admin.pagination :paginator="$items" label="barang sewa" />
    </section>
</x-layouts.admin>