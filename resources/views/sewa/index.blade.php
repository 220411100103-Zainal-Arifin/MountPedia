@php
    
    $ratios = ['4 / 5', '3 / 4', '1 / 1'];
    $tones = ['#1f5a40', '#2b4f6b', '#6b4a2b', '#4c3a6b', '#6b2b3a', '#2b6b64'];
    $storeIcon = '<path d="M3 9l1.5-5h15L21 9"/><path d="M3 9a3 3 0 006 0 3 3 0 006 0 3 3 0 006 0"/><path d="M5 12v8h14v-8M10 20v-5h4v5"/>';
@endphp
<x-layouts.app
    title="Sewa Alat Pendakian"
    description="Daftar tempat penyewaan alat pendakian beserta lokasi, jam buka, dan gunung yang dilayani."
    :assets="['resources/css/gunung.css', 'resources/css/perlengkapan.css', 'resources/css/sewa.css', 'resources/js/perlengkapan.js']"
    :overlay="true"
    :footer="false"
>
    <main class="explorer">
        <div class="explorer__backdrop" aria-hidden="true"></div>

        <header class="explorer__head container">
            <p class="eyebrow">Rental Guide</p>
            <h1 class="explorer__title">Sewa Alat Pendakian</h1>
            <p class="explorer__lead">
                Tempat penyewaan alat di sekitar basecamp. Cek lokasi, jam buka, dan gunung yang dilayani
                sebelum berangkat.
            </p>
        </header>

        <section class="explorer__filters container" aria-label="Pencarian dan filter">
            <form method="GET" action="{{ route('rental.index') }}" role="search" data-filter-form>
                <div class="search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="Cari nama toko, alamat, atau kota"
                        aria-label="Cari tempat sewa"
                        maxlength="80"
                        autocomplete="off"
                    >
                    <button class="search__btn" type="submit">Cari</button>
                </div>

                <div class="filters__row">
                    <label class="select">
                        <span class="sr-only">Provinsi</span>
                        <select name="provinsi" data-autosubmit @class(['is-active' => $filters['provinsi'] !== ''])>
                            <option value="">Semua provinsi</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province }}" @selected($filters['provinsi'] === $province)>{{ $province }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="select">
                        <span class="sr-only">Gunung</span>
                        <select name="gunung" data-autosubmit @class(['is-active' => $filters['gunung'] !== ''])>
                            <option value="">Semua gunung</option>
                            @foreach ($mountains as $mountain)
                                <option value="{{ $mountain->slug }}" @selected($filters['gunung'] === $mountain->slug)>{{ $mountain->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="chips chips--inline">
                        <label class="chip">
                            <input type="checkbox" name="verified" value="1" data-autosubmit @checked($filters['verified'] === '1')>
                            <span>Terverifikasi</span>
                        </label>
                    </div>
                </div>
            </form>
        </section>

        <section class="container" aria-label="Daftar tempat sewa">
            <div class="explorer__bar">
                <p class="count" aria-live="polite">
                    <strong>{{ number_format($shops->total(), 0, ',', '.') }}</strong> tempat sewa ditemukan
                </p>
                @if ($hasFilters)
                    <a class="reset" href="{{ route('rental.index') }}">Hapus filter</a>
                @endif
            </div>

            @if ($shops->isEmpty())
                <div class="empty">
                    <p class="empty__title">Belum ada tempat sewa yang cocok</p>
                    <p class="empty__text">Coba kata kunci lain atau longgarkan filter yang sedang dipakai.</p>
                    <a class="empty__link" href="{{ route('rental.index') }}">Lihat semua tempat sewa</a>
                </div>
            @else
                <ul class="masonry" role="list" data-ratios="{{ implode('|', $ratios) }}">
                    @php $prev = -1; @endphp
                    @foreach ($shops as $shop)
                        @php
                            $pick = crc32((string) $shop->slug) % count($ratios);
                            if ($pick === $prev) {
                                $pick = ($pick + 1) % count($ratios);
                            }
                            $prev = $pick;

                            $photo = $shop->image_url ?: null;
                            $near = $mountainsByShop->get($shop->id, collect())->pluck('name');
                        @endphp
                        <li class="mx-item" style="--i: {{ $loop->index }}">
                            <a
                                class="mx-card @unless ($photo) mx-card--empty @endunless"
                                href="{{ url('/sewa/'.$shop->slug) }}"
                                style="--ratio: {{ $ratios[$pick] }}; --tone: {{ $tones[crc32((string) $shop->slug) % count($tones)] }}"
                                @if ($photo) data-image="{{ $photo }}" @endif
                            >
                                <span class="mx-card__media" aria-hidden="true">
                                    @unless ($photo)
                                        <svg class="mx-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">{!! $storeIcon !!}</svg>
                                    @endunless
                                </span>

                                @if ($shop->is_verified)
                                    <span class="mx-card__badge" data-tone="verified"><i class="dot"></i>Terverifikasi</span>
                                @endif

                                <span class="mx-card__body">
                                    <span class="mx-card__name">{{ $shop->name }}</span>
                                    <span class="mx-card__meta">
                                        <span class="mx-card__place">{{ $shop->regency }}, {{ $shop->province }}</span>
                                        @if ($shop->min_price)
                                            <span class="mx-card__elev">Mulai Rp {{ number_format($shop->min_price, 0, ',', '.') }}/hari</span>
                                        @endif
                                    </span>
                                    @if ($shop->opening_hours)
                                        <span class="mx-card__line">Buka: <strong>{{ $shop->opening_hours }}</strong></span>
                                    @endif
                                    @if ($near->isNotEmpty())
                                        <span class="mx-card__line">
                                            Melayani {{ $near->take(2)->implode(', ') }}@if ($near->count() > 2) +{{ $near->count() - 2 }} lainnya @endif
                                        </span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($shops->hasPages())
                    @php
                        $current = $shops->currentPage();
                        $last = $shops->lastPage();
                        $pages = collect([1, $last, $current - 1, $current, $current + 1])
                            ->filter(fn ($p) => $p >= 1 && $p <= $last)
                            ->unique()->sort()->values();
                    @endphp
                    <nav class="pager" aria-label="Halaman daftar tempat sewa">
                        @if ($shops->onFirstPage())
                            <span class="pager__step is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></span>
                        @else
                            <a class="pager__step" href="{{ $shops->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></a>
                        @endif

                        <ol class="pager__pages">
                            @foreach ($pages as $p)
                                @if (! $loop->first && $p - $pages[$loop->index - 1] > 1)
                                    <li class="pager__gap" aria-hidden="true">…</li>
                                @endif
                                <li>
                                    @if ($p === $current)
                                        <span class="is-current" aria-current="page">{{ $p }}</span>
                                    @else
                                        <a href="{{ $shops->url($p) }}" aria-label="Halaman {{ $p }}">{{ $p }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ol>

                        @if ($shops->hasMorePages())
                            <a class="pager__step" href="{{ $shops->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></a>
                        @else
                            <span class="pager__step is-disabled" aria-disabled="true" aria-label="Halaman berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></span>
                        @endif
                    </nav>
                @endif
            @endif
        </section>
    </main>
</x-layouts.app>
