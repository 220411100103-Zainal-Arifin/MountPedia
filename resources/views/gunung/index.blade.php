@php
    // Rasio foto bervariasi; dipilih stabil dari slug dan tidak pernah sama dengan kartu sebelumnya
    $ratios = ['4 / 5', '3 / 4', '1 / 1', '4 / 3'];
    $statusLabels = ['buka' => 'Dibuka', 'tutup' => 'Ditutup', 'dibatasi' => 'Dibatasi'];
    $statusChips = ['' => 'Semua', 'buka' => 'Dibuka', 'dibatasi' => 'Dibatasi', 'tutup' => 'Ditutup'];
@endphp
<x-layouts.app
    title="Jelajahi Pegunungan Indonesia"
    description="Cari gunung di Indonesia berdasarkan nama, provinsi, ketinggian, dan status pendakian."
    :assets="['resources/css/gunung.css', 'resources/js/gunung.js']"
    :overlay="true"
    :footer="false"
>
    <main class="explorer">
        <div class="explorer__backdrop" aria-hidden="true"></div>

        <header class="explorer__head container">
            <p class="eyebrow">Mountain Explorer</p>
            <h1 class="explorer__title">Jelajahi Pegunungan Indonesia</h1>
            <p class="explorer__lead">
                Temukan gunung untuk pendakian berikutnya, dari bukit hijau yang ramah pemula hingga puncak
                berapi di atas 3.000 mdpl. Periksa ketinggian, wilayah, dan status pendakian sebelum berangkat.
            </p>
        </header>

        <section class="explorer__filters container" aria-label="Pencarian dan filter">
            <form method="GET" action="{{ route('mountains.index') }}" role="search" data-filter-form>
                <div class="search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="Cari nama gunung atau kabupaten"
                        aria-label="Cari nama gunung atau kabupaten"
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
                        <span class="sr-only">Ketinggian</span>
                        <select name="mdpl" data-autosubmit @class(['is-active' => $filters['mdpl'] !== ''])>
                            <option value="">Semua ketinggian</option>
                            @foreach ($elevationRanges as $key => $range)
                                <option value="{{ $key }}" @selected($filters['mdpl'] === $key)>{{ $range[2] }}</option>
                            @endforeach
                        </select>
                    </label>

                    <fieldset class="chips">
                        <legend class="sr-only">Status pendakian</legend>
                        @foreach ($statusChips as $value => $label)
                            <label class="chip">
                                <input type="radio" name="status" value="{{ $value }}" data-autosubmit @checked($filters['status'] === $value)>
                                <span>
                                    @if ($value !== '')
                                        <i class="dot" data-status="{{ $value }}"></i>
                                    @endif
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </fieldset>
                </div>
            </form>
        </section>

        <section class="container" aria-label="Daftar gunung">
            <div class="explorer__bar">
                <p class="count" aria-live="polite">
                    <strong>{{ number_format($mountains->total(), 0, ',', '.') }}</strong> gunung ditemukan
                </p>
                @if ($hasFilters)
                    <a class="reset" href="{{ route('mountains.index') }}">Hapus filter</a>
                @endif
            </div>

            @if ($mountains->isEmpty())
                <div class="empty">
                    <p class="empty__title">Belum ada gunung yang cocok</p>
                    <p class="empty__text">Coba kata kunci lain atau longgarkan filter yang sedang dipakai.</p>
                    <a class="empty__link" href="{{ route('mountains.index') }}">Lihat semua gunung</a>
                </div>
            @else
                <ul class="masonry" role="list">
                    @php $prev = -1; @endphp
                    @foreach ($mountains as $m)
                        @php
                            $pick = crc32($m->slug) % count($ratios);
                            if ($pick === $prev) {
                                $pick = ($pick + 1) % count($ratios);
                            }
                            $prev = $pick;

                            $place = Str::replaceFirst('Kabupaten ', '', $m->region->regency).', '.$m->region->province;
                            // image_url dari seeder hanya placeholder; abaikan agar kartu memakai ilustrasi bawaan
                            $photo = str_contains((string) $m->image_url, 'placehold.co') ? null : $m->image_url;
                        @endphp
                        <li class="mx-item" style="--i: {{ $loop->index }}">
                            <a
                                class="mx-card"
                                href="{{ url('/gunung/'.$m->slug) }}"
                                style="--ratio: {{ $ratios[$pick] }}"
                                data-slug="{{ $m->slug }}"
                                @if ($photo) data-image="{{ $photo }}" @endif
                            >
                                <span class="mx-card__media" aria-hidden="true"></span>

                                <span class="mx-card__badge" data-status="{{ $m->current_status }}">
                                    <i class="dot"></i>{{ $statusLabels[$m->current_status] ?? 'Status belum ada' }}
                                </span>

                                <span class="mx-card__body">
                                    <span class="mx-card__name">{{ $m->name }}</span>
                                    <span class="mx-card__meta">
                                        <span class="mx-card__place">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11a7 7 0 0114 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                            {{ $place }}
                                        </span>
                                        <span class="mx-card__elev">{{ number_format($m->elevation_mdpl, 0, ',', '.') }} mdpl</span>
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($mountains->hasPages())
                    @php
                        $current = $mountains->currentPage();
                        $last = $mountains->lastPage();
                        $pages = collect([1, $last, $current - 1, $current, $current + 1])
                            ->filter(fn ($p) => $p >= 1 && $p <= $last)
                            ->unique()->sort()->values();
                    @endphp
                    <nav class="pager" aria-label="Halaman daftar gunung">
                        @if ($mountains->onFirstPage())
                            <span class="pager__step is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></span>
                        @else
                            <a class="pager__step" href="{{ $mountains->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></a>
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
                                        <a href="{{ $mountains->url($p) }}" aria-label="Halaman {{ $p }}">{{ $p }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ol>

                        @if ($mountains->hasMorePages())
                            <a class="pager__step" href="{{ $mountains->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></a>
                        @else
                            <span class="pager__step is-disabled" aria-disabled="true" aria-label="Halaman berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></span>
                        @endif
                    </nav>
                @endif
            @endif
        </section>
    </main>
</x-layouts.app>