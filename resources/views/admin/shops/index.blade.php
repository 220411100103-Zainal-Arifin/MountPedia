<x-layouts.admin title="Tempat Sewa" section="Master Data">
    <x-admin.page-head text="Kelola data tempat penyewaan alat pendakian, kontak, dan status verifikasinya.">
        <span class="btn btn--primary is-disabled" title="Tersedia di tahap berikutnya"><x-admin.icon name="plus" /> Tambah Tempat Sewa</span>
    </x-admin.page-head>

    <section class="panel">
        <form class="toolbar" method="GET" action="{{ route('admin.shops.index') }}" data-filter-form>
            <label class="tb-search">
                <x-admin.icon name="search" />
                <input type="search" name="q" value="{{ $filters['q'] }}" maxlength="80" autocomplete="off"
                       placeholder="Cari nama tempat sewa atau kabupaten" aria-label="Cari">
            </label>
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
                <span class="sr-only">Verifikasi</span>
                <select name="verifikasi" data-autosubmit>
                    <option value="">Semua status</option>
                    <option value="1" @selected($filters['verifikasi'] === '1')>Terverifikasi</option>
                    <option value="0" @selected($filters['verifikasi'] === '0')>Belum terverifikasi</option>
                </select>
            </label>
            @if ($hasFilters)
                <a class="toolbar__reset" href="{{ route('admin.shops.index') }}">Hapus filter</a>
            @endif
            <p class="toolbar__note">{{ number_format($total, 0, ',', '.') }} tempat sewa · {{ number_format($unverified, 0, ',', '.') }} belum terverifikasi</p>
        </form>

        @if ($shops->isEmpty())
            <div class="empty"><strong>Belum ada data yang cocok</strong>Coba kata kunci lain atau hapus filter yang dipakai.</div>
        @else
            <div class="table-wrap">
                <table class="table table--cards">
                    <thead>
                        <tr>
                            <th>Tempat sewa</th>
                            <th>Kontak</th>
                            <th class="num">Barang</th>
                            <th>Rating</th>
                            <th>Verifikasi</th>
                            <th><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shops as $s)
                            <tr>
                                <td class="cell--title">
                                    <div class="cell-main">
                                        <span class="thumb">{{ Str::upper(Str::substr($s->name, 0, 1)) }}</span>
                                        <div class="cell-title">
                                            <strong>{{ $s->name }}</strong>
                                            <small>{{ Str::replaceFirst('Kabupaten ', '', $s->regency) }}, {{ $s->province }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Kontak">
                                    <div class="cell-title">
                                        <strong>{{ $s->whatsapp ?: ($s->phone ?: '—') }}</strong>
                                        @if ($s->opening_hours)<small>{{ $s->opening_hours }}</small>@endif
                                    </div>
                                </td>
                                <td class="num" data-label="Barang">{{ $s->items_count }}</td>
                                <td data-label="Rating">
                                    @if ($s->reviews_count > 0)
                                        <span class="rating"><x-admin.icon name="star" /> {{ number_format($s->rating_avg, 1, ',', '.') }}
                                            <small>({{ $s->reviews_count }})</small></span>
                                    @else
                                        <span class="muted">Belum ada</span>
                                    @endif
                                </td>
                                <td data-label="Verifikasi"><x-admin.badge :tone="$s->is_verified ? 'green' : 'amber'">{{ $s->is_verified ? 'Terverifikasi' : 'Belum' }}</x-admin.badge></td>
                                <td class="cell--actions"><x-admin.row-actions :view="route('rental.show', $s->slug)" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <x-admin.pagination :paginator="$shops" label="tempat sewa" />
    </section>
</x-layouts.admin>