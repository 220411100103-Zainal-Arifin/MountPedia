@php
    $ratios = ['4 / 5', '3 / 4', '1 / 1', '4 / 3'];
    $priorityLabels = ['wajib' => 'Wajib', 'disarankan' => 'Disarankan', 'opsional' => 'Opsional'];
    $tripChips = ['' => 'Semua', 'tektok' => 'Tektok', 'camping' => 'Camping'];

    // warna dasar kartu yang belum punya foto, bergilir menurut kategori
    $tones = ['#1f5a40', '#2b4f6b', '#6b4a2b', '#4c3a6b', '#6b2b3a', '#2b6b64'];

    // ikon sementara per kategori (stroke); kategori lain memakai ikon gunung
    $icons = [
        'Pakaian' => '<path d="M8 4l-5 3 2 4 3-1v10h8V10l3 1 2-4-5-3a4 4 0 01-8 0z"/>',
        'Tas & Shelter' => '<path d="M3 20L12 4l9 16H3z"/><path d="M12 4v16M9 20l3-6 3 6"/>',
        'Navigasi & Keselamatan' => '<circle cx="12" cy="12" r="9"/><path d="M15.5 8.5l-2 5-5 2 2-5 5-2z"/>',
        'Masak & Makan' => '<path d="M12 3c1 3 5 5 5 10a5 5 0 01-10 0c0-2 1-3 2-4 .5 1 1 1.5 2 1.5C11 8 11 5 12 3z"/>',
        'Penerangan & Daya' => '<path d="M13 3L5 14h6l-1 7 8-11h-6l1-7z"/>',
        'Logistik' => '<path d="M4 8l8-4 8 4v8l-8 4-8-4V8z"/><path d="M4 8l8 4 8-4M12 12v8"/>',
    ];
    $fallbackIcon = '<path d="M3 19l6-10 4 6 3-4 5 8H3z"/>';
@endphp
<x-layouts.app
    title="Perlengkapan Pendakian"
    description="Daftar perlengkapan pendakian untuk tektok dan camping, lengkap dengan gambar dan informasi sewa."
    :assets="['resources/css/gunung.css', 'resources/css/perlengkapan.css', 'resources/js/perlengkapan.js']"
    :overlay="true"
    :footer="false"
>
    <main class="explorer">
        <div class="explorer__backdrop" aria-hidden="true"></div>

        <header class="explorer__head container">
            <p class="eyebrow">Gear Guide</p>
            <h1 class="explorer__title">Perlengkapan Pendakian</h1>
            <p class="explorer__lead">
                Daftar peralatan untuk pendakian tektok maupun camping. Lihat gambarnya, pilih sesuai jenis
                pendakianmu, dan cek mana yang bisa disewa sebelum berangkat.
            </p>
        </header>

        <section class="explorer__filters container" aria-label="Pencarian dan filter">
            <form method="GET" action="{{ route('gear.index') }}" role="search" data-filter-form>
                <div class="search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] }}"
                        placeholder="Cari nama perlengkapan"
                        aria-label="Cari nama perlengkapan"
                        maxlength="80"
                        autocomplete="off"
                    >
                    <button class="search__btn" type="submit">Cari</button>
                </div>

                <div class="filters__row">
                    <label class="select">
                        <span class="sr-only">Kategori</span>
                        <select name="kategori" data-autosubmit @class(['is-active' => $filters['kategori'] !== ''])>
                            <option value="">Semua kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($filters['kategori'] === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <div class="chips chips--inline">
                        <label class="chip">
                            <input type="checkbox" name="sewa" value="1" data-autosubmit @checked($filters['sewa'] === '1')>
                            <span>Bisa disewa</span>
                        </label>
                    </div>

                    <fieldset class="chips">
                        <legend class="sr-only">Jenis pendakian</legend>
                        @foreach ($tripChips as $value => $label)
                            <label class="chip">
                                <input type="radio" name="jenis" value="{{ $value }}" data-autosubmit @checked($filters['jenis'] === $value)>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </fieldset>
                </div>
            </form>
        </section>

        <section class="container" aria-label="Daftar perlengkapan">
            <div class="explorer__bar">
                <p class="count" aria-live="polite">
                    <strong>{{ number_format($items->total(), 0, ',', '.') }}</strong> perlengkapan ditemukan
                </p>
                @if ($hasFilters)
                    <a class="reset" href="{{ route('gear.index') }}">Hapus filter</a>
                @endif
            </div>

            @if ($items->isEmpty())
                <div class="empty">
                    <p class="empty__title">Belum ada perlengkapan yang cocok</p>
                    <p class="empty__text">Coba kata kunci lain atau longgarkan filter yang sedang dipakai.</p>
                    <a class="empty__link" href="{{ route('gear.index') }}">Lihat semua perlengkapan</a>
                </div>
            @else
                <ul class="masonry" role="list">
                    @php $prev = -1; @endphp
                    @foreach ($items as $item)
                        @php
                            $pick = crc32((string) $item->slug) % count($ratios);
                            if ($pick === $prev) {
                                $pick = ($pick + 1) % count($ratios);
                            }
                            $prev = $pick;

                            $photo = $item->image_url ?: null;
                        @endphp
                        <li class="mx-item" style="--i: {{ $loop->index }}">
                            <a
                                class="mx-card @unless ($photo) mx-card--empty @endunless"
                                href="{{ url('/perlengkapan/'.$item->slug) }}"
                                style="--ratio: {{ $ratios[$pick] }}; --tone: {{ $tones[($item->category_id - 1) % count($tones)] }}"
                                @if ($photo) data-image="{{ $photo }}" @endif
                            >
                                <span class="mx-card__media" aria-hidden="true">
                                    @unless ($photo)
                                        <svg class="mx-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$item->category_name] ?? $fallbackIcon !!}</svg>
                                    @endunless
                                </span>

                                @if ($tripType && $item->priority)
                                    <span class="mx-card__badge" data-tone="{{ $item->priority }}">
                                        <i class="dot"></i>{{ $priorityLabels[$item->priority] ?? $item->priority }}
                                    </span>
                                @endif

                                <span class="mx-card__body">
                                    <span class="mx-card__name">{{ $item->name }}</span>
                                    <span class="mx-card__meta">
                                        <span class="mx-card__place">{{ $item->category_name }}</span>
                                        @if ($item->is_rentable)
                                            <span class="mx-card__elev">Bisa disewa</span>
                                        @endif
                                    </span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($items->hasPages())
                    @php
                        $current = $items->currentPage();
                        $last = $items->lastPage();
                        $pages = collect([1, $last, $current - 1, $current, $current + 1])
                            ->filter(fn ($p) => $p >= 1 && $p <= $last)
                            ->unique()->sort()->values();
                    @endphp
                    <nav class="pager" aria-label="Halaman daftar perlengkapan">
                        @if ($items->onFirstPage())
                            <span class="pager__step is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></span>
                        @else
                            <a class="pager__step" href="{{ $items->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></a>
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
                                        <a href="{{ $items->url($p) }}" aria-label="Halaman {{ $p }}">{{ $p }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ol>

                        @if ($items->hasMorePages())
                            <a class="pager__step" href="{{ $items->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></a>
                        @else
                            <span class="pager__step is-disabled" aria-disabled="true" aria-label="Halaman berikutnya"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></span>
                        @endif
                    </nav>
                @endif
            @endif
        </section>
    </main>
</x-layouts.app>
