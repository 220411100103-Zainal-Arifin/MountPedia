@php
    $location = $shop->regency.', '.$shop->province;
    $mainPhotos = array_slice($photos, 0, 3);
    $hasRating = $reviewStats->count > 0;
    $nav = [
        'tentang' => 'Tentang',
        'katalog' => 'Katalog',
        'harga' => 'Harga & ketentuan',
        'lokasi' => 'Lokasi',
        'ulasan' => 'Ulasan',
    ];
    $hasContact = filled($waGeneral) || filled($phoneDigits);
    $paragraphs = $description !== '' ? array_values(array_filter(preg_split('/\R{2,}/u', $description))) : [];
@endphp
<x-layouts.app
    :title="$shop->name"
    :description="$metaDescription"
    :assets="['resources/css/sewa-detail.css', 'resources/js/sewa-detail.js']"
>
    <main class="shop">
        <div class="container">

            {{-- 2. Galeri hero: satu foto besar + dua pendukung --}}
            @if ($photos)
                <div class="gallery gallery--{{ count($mainPhotos) }}">
                    @foreach ($mainPhotos as $i => $photo)
                        <button type="button" class="gallery__item gallery__item--{{ $i }}" data-lightbox-open="{{ $i }}" aria-label="Perbesar foto {{ $i + 1 }}">
                            <img
                                src="{{ $photo['url'] }}"
                                alt="{{ $photo['caption'] !== '' ? $photo['caption'] : $shop->name.', foto '.($i + 1) }}"
                                @if ($i > 0) loading="lazy" @endif
                                decoding="async"
                            >
                        </button>
                    @endforeach
                    <button type="button" class="gallery__more" data-lightbox-open="0">
                        <x-sewa.icon name="image" :size="16" />
                        {{ count($photos) > 3 ? 'Lihat semua '.count($photos).' foto' : 'Perbesar foto' }}
                    </button>
                </div>
            @else
                <x-sewa.empty icon="image" class="gallery-empty" title="Foto tempat sewa belum tersedia" text="Hubungi pemilik untuk melihat kondisi tempat dan perlengkapannya." />
            @endif

            {{-- 3 & 4. Identitas + CTA --}}
            <header class="identity">
                <div class="identity__main">
                    <p class="identity__kicker">
                        Tempat sewa perlengkapan
                        @if ($shop->is_verified)
                            <span class="verified"><x-sewa.icon name="verified" :size="15" /> Terverifikasi</span>
                        @endif
                    </p>
                    <h1 class="identity__name">{{ $shop->name }}</h1>
                    <ul class="identity__meta">
                        <li><x-sewa.icon name="pin" :size="16" /> {{ $location }}</li>
                        <li>
                            @if ($hasRating)
                                <x-sewa.stars :rating="$reviewStats->avg" :size="15" />
                                <a href="#ulasan"><strong>{{ number_format($reviewStats->avg, 1, ',', '.') }}</strong> ({{ $reviewStats->count }} ulasan)</a>
                            @else
                                <span class="muted">Belum ada ulasan</span>
                            @endif
                        </li>
                        @if ($status)
                            <li>
                                <span @class(['status', 'status--open' => $status['open']])>
                                    <i></i>{{ $status['label'] }}@if ($status['detail']) <span class="status__detail">· {{ $status['detail'] }}</span>@endif
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>

                @if ($hasContact || $directionsUrl)
                    <div class="identity__cta">
                        @if ($waGeneral)
                            <a class="btn btn--primary" href="{{ $waGeneral }}" target="_blank" rel="noopener">
                                <x-sewa.icon name="whatsapp" /> Hubungi Pemilik
                            </a>
                        @endif
                        @if ($directionsUrl)
                            <a class="btn btn--line" href="{{ $directionsUrl }}" target="_blank" rel="noopener">
                                <x-sewa.icon name="directions" /> Petunjuk Arah
                            </a>
                        @endif
                    </div>
                @endif
            </header>

            {{-- 5. Navigasi sticky --}}
            <nav class="subnav" aria-label="Bagian halaman" data-subnav>
                <ul>
                    @foreach ($nav as $id => $label)
                        <li><a href="#{{ $id }}" data-subnav-link="{{ $id }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div class="shop__layout">
                <div class="shop__main">

                    {{-- 6. Tentang: tata letak editorial --}}
                    <x-sewa.section id="tentang" eyebrow="Tentang" title="Tentang tempat sewa ini">
                        <div class="about">
                            <div class="about__text">
                                @forelse ($paragraphs as $i => $p)
                                    <p @class(['about__lead' => $i === 0])>{{ trim($p) }}</p>
                                @empty
                                    <x-sewa.empty title="Deskripsi belum tersedia" text="Pemilik belum menambahkan penjelasan tentang tempat sewa ini." />
                                @endforelse
                            </div>

                            <dl class="facts">
                                <div><dt>Lokasi</dt><dd>{{ $location }}</dd></div>
                                <div><dt>Jumlah barang</dt><dd>{{ count($items) ? count($items).' barang di '.count($categories).' kategori' : 'Belum ada data' }}</dd></div>
                                <div>
                                    <dt>Melayani gunung</dt>
                                    <dd>
                                        @if ($mountains)
                                            <ul class="chip-list">
                                                @foreach ($mountains as $m)
                                                    <li><a href="{{ url('/gunung/'.$m->slug) }}">{{ $m->name }}</a></li>
                                                @endforeach
                                            </ul>
                                        @else
                                            Belum ada data
                                        @endif
                                    </dd>
                                </div>
                                <div><dt>Verifikasi</dt><dd>{{ $shop->is_verified ? 'Data telah diverifikasi' : 'Belum diverifikasi' }}</dd></div>
                            </dl>
                        </div>
                    </x-sewa.section>

                    {{-- 7 & 8. Katalog: grid standar + filter + pencarian --}}
                    <x-sewa.section id="katalog" eyebrow="Katalog" title="Perlengkapan yang disewakan">
                        @if ($items)
                            <div class="toolbar">
                                <label class="toolbar__search">
                                    <span class="sr-only">Cari barang</span>
                                    <x-sewa.icon name="search" :size="18" />
                                    <input type="search" placeholder="Cari barang, mis. tenda" maxlength="60" autocomplete="off" data-catalog-search>
                                </label>
                                <div class="pills" role="group" aria-label="Filter kategori">
                                    <button type="button" class="pill is-active" data-cat-btn="" aria-pressed="true">Semua <span>{{ count($items) }}</span></button>
                                    @foreach ($categories as $c)
                                        <button type="button" class="pill" data-cat-btn="{{ $c->key }}" aria-pressed="false">{{ $c->name }} <span>{{ $c->count }}</span></button>
                                    @endforeach
                                </div>
                            </div>

                            <p class="catalog-count" aria-live="polite"><strong data-catalog-count>{{ count($items) }}</strong> barang ditampilkan</p>

                            <div class="catalog" data-catalog>
                                @foreach ($items as $item)
                                    <x-sewa.product-card :item="$item" />
                                @endforeach
                            </div>

                            <div class="catalog-none" data-catalog-none hidden>
                                <x-sewa.empty icon="search" title="Barang tidak ditemukan" text="Coba kata kunci lain atau pilih kategori Semua." />
                            </div>

                            <p class="fineprint">
                                <x-sewa.icon name="info" :size="15" />
                                Ketersediaan dan stok dapat berubah sewaktu-waktu. Konfirmasi ke pemilik sebelum datang.
                            </p>
                        @else
                            <x-sewa.empty icon="gear" title="Katalog belum tersedia" text="Tempat sewa ini belum mencantumkan daftar barang. Tanyakan langsung ke pemilik.">
                                @if ($waGeneral)
                                    <a class="btn btn--line btn--sm" href="{{ $waGeneral }}" target="_blank" rel="noopener"><x-sewa.icon name="whatsapp" :size="16" /> Tanyakan lewat WhatsApp</a>
                                @endif
                            </x-sewa.empty>
                        @endif
                    </x-sewa.section>

                    {{-- 9 & 10. Harga, ketentuan, ketersediaan --}}
                    <x-sewa.section id="harga" eyebrow="Harga" title="Harga sewa & ketentuan">
                        <div class="pricing">
                            <div class="pricing__table">
                                <h3 class="h3">Kisaran harga per kategori</h3>
                                @if ($categories)
                                    <table class="table">
                                        <thead>
                                            <tr><th scope="col">Kategori</th><th scope="col" class="num">Barang</th><th scope="col" class="num">Harga / hari</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($categories as $c)
                                                <tr>
                                                    <th scope="row">{{ $c->name }}</th>
                                                    <td class="num">{{ $c->count }}</td>
                                                    <td class="num">
                                                        @if ($c->min === $c->max)
                                                            Rp {{ number_format($c->min, 0, ',', '.') }}
                                                        @else
                                                            Rp {{ number_format($c->min, 0, ',', '.') }} – {{ number_format($c->max, 0, ',', '.') }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <x-sewa.empty icon="info" title="Daftar harga belum tersedia" text="Harga akan tampil setelah pemilik menambahkan katalog." />
                                @endif
                            </div>

                            <div class="pricing__terms">
                                <h3 class="h3">Ketentuan sewa</h3>
                                @if ($terms)
                                    <ol class="terms">
                                        @foreach ($terms as $term)
                                            <li>{{ $term }}</li>
                                        @endforeach
                                    </ol>
                                @else
                                    <x-sewa.empty icon="info" title="Ketentuan belum dicantumkan" text="Tanyakan syarat sewa, jaminan, dan denda keterlambatan langsung ke pemilik." />
                                @endif
                            </div>
                        </div>
                    </x-sewa.section>

                    {{-- 11. Peta (Leaflet) --}}
                    <x-sewa.section id="lokasi" eyebrow="Lokasi" title="Peta & alamat">
                        <div class="where">
                            @if ($hasCoords)
                                <div
                                    class="map"
                                    data-map
                                    data-lat="{{ $shop->latitude }}"
                                    data-lng="{{ $shop->longitude }}"
                                    data-name="{{ $shop->name }}"
                                    role="region"
                                    aria-label="Peta lokasi {{ $shop->name }}"
                                ></div>
                            @else
                                <x-sewa.empty icon="pin" title="Titik peta belum tersedia" text="Koordinat tempat ini belum dicatat. Gunakan alamat di bawah untuk mencarinya." />
                            @endif
                            <div class="where__row">
                                <p class="where__address">
                                    <x-sewa.icon name="pin" :size="16" />
                                    @if (filled($shop->address))
                                        {{ $shop->address }}, {{ $location }}
                                    @else
                                        {{ $location }} <span class="muted">(alamat lengkap belum dicatat)</span>
                                    @endif
                                </p>
                                @if ($directionsUrl)
                                    <a class="link-arrow" href="{{ $directionsUrl }}" target="_blank" rel="noopener">Buka petunjuk arah <x-sewa.icon name="arrow" :size="16" /></a>
                                @endif
                            </div>
                        </div>
                    </x-sewa.section>

                    {{-- 13. Ulasan --}}
                    <x-sewa.section id="ulasan" eyebrow="Ulasan" title="Ulasan pengunjung">
                        @if ($hasRating)
                            <div class="reviews">
                                <div class="reviews__summary">
                                    <p class="reviews__score">{{ number_format($reviewStats->avg, 1, ',', '.') }}</p>
                                    <x-sewa.stars :rating="$reviewStats->avg" :size="18" />
                                    <p class="muted">{{ $reviewStats->count }} ulasan</p>
                                    <ul class="bars">
                                        @foreach ($reviewStats->dist as $star => $n)
                                            <li>
                                                <span>{{ $star }}</span>
                                                <i style="--w: {{ $reviewStats->count ? round($n / $reviewStats->count * 100) : 0 }}%"></i>
                                                <span class="num">{{ $n }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <ul class="reviews__list" data-reviews>
                                    @foreach ($reviews as $i => $r)
                                        <li class="review" @if ($i >= 4) data-extra hidden @endif>
                                            <div class="review__top">
                                                <strong>{{ $r->name }}</strong>
                                                <span class="muted">{{ $r->date }}</span>
                                            </div>
                                            <x-sewa.stars :rating="$r->rating" :size="14" />
                                            @if (filled($r->comment))
                                                <p>{{ $r->comment }}</p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @if (count($reviews) > 4)
                                <button type="button" class="btn btn--line btn--sm" data-reviews-more>Tampilkan semua ulasan</button>
                            @endif
                        @else
                            <x-sewa.empty icon="info" title="Belum ada ulasan" text="Ulasan pengunjung akan tampil di sini." />
                        @endif
                    </x-sewa.section>
                </div>

                {{-- Sidebar sticky: kontak, jam, lokasi --}}
                <aside class="aside" aria-label="Kontak dan informasi">
                    <div class="aside__panel">
                        <section class="aside__block">
                            <h2 class="aside__title">Hubungi pemilik</h2>
                            @if ($hasContact)
                                @if ($waGeneral)
                                    <a class="btn btn--primary btn--block" href="{{ $waGeneral }}" target="_blank" rel="noopener">
                                        <x-sewa.icon name="whatsapp" /> Chat WhatsApp
                                    </a>
                                @endif
                                @if ($phoneDigits)
                                    <a class="aside__line" href="tel:{{ $phoneDigits }}"><x-sewa.icon name="phone" :size="16" /> {{ $shop->phone }}</a>
                                @endif
                            @else
                                <p class="muted">Kontak pemilik belum dicantumkan.</p>
                            @endif
                        </section>

                        <section class="aside__block">
                            <h2 class="aside__title">Jam operasional</h2>
                            @if (filled($shop->opening_hours))
                                <p class="aside__line"><x-sewa.icon name="clock" :size="16" /> {{ $shop->opening_hours }}</p>
                                @if ($status)
                                    <p @class(['status', 'status--open' => $status['open']])>
                                        <i></i>{{ $status['label'] }}@if ($status['detail']) <span class="status__detail">· {{ $status['detail'] }}</span>@endif
                                    </p>
                                @endif
                            @else
                                <p class="muted">Jam operasional belum dicantumkan.</p>
                            @endif
                        </section>

                        <section class="aside__block">
                            <h2 class="aside__title">Lokasi</h2>
                            <p class="aside__line"><x-sewa.icon name="pin" :size="16" />
                                <span>
                                    @if (filled($shop->address)) {{ $shop->address }}<br> @endif
                                    {{ $location }}
                                </span>
                            </p>
                            @if ($directionsUrl)
                                <a class="btn btn--line btn--block" href="{{ $directionsUrl }}" target="_blank" rel="noopener">
                                    <x-sewa.icon name="directions" /> Petunjuk arah
                                </a>
                            @endif
                        </section>

                        @if ($mountains)
                            <section class="aside__block">
                                <h2 class="aside__title">Dekat basecamp</h2>
                                <ul class="aside__list">
                                    @foreach ($mountains as $m)
                                        <li>
                                            <a href="{{ url('/gunung/'.$m->slug) }}">{{ $m->name }}</a>
                                            @if ($m->km !== null)
                                                <span class="muted">{{ number_format($m->km, 1, ',', '.') }} km</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif

                        @if ($updatedLabel)
                            <p class="aside__stamp">Informasi diperbarui {{ $updatedLabel }}</p>
                        @endif
                    </div>
                </aside>
            </div>
        </div>

        {{-- Bilah CTA untuk layar kecil --}}
        @if ($waGeneral || $directionsUrl)
            <div class="ctabar">
                @if ($waGeneral)
                    <a class="btn btn--primary" href="{{ $waGeneral }}" target="_blank" rel="noopener"><x-sewa.icon name="whatsapp" /> Hubungi Pemilik</a>
                @endif
                @if ($directionsUrl)
                    <a class="btn btn--line" href="{{ $directionsUrl }}" target="_blank" rel="noopener"><x-sewa.icon name="directions" /> Arah</a>
                @endif
            </div>
        @endif
    </main>

    @if ($photos)
        <dialog class="lightbox" data-lightbox data-photos="{{ json_encode($photos) }}" aria-label="Galeri foto {{ $shop->name }}">
            <button type="button" class="lightbox__close" data-lightbox-close aria-label="Tutup"><x-sewa.icon name="close" :size="22" /></button>
            <button type="button" class="lightbox__nav lightbox__nav--prev" data-lightbox-prev aria-label="Foto sebelumnya"><x-sewa.icon name="chevron" :size="26" /></button>
            <figure class="lightbox__figure">
                <img src="" alt="" data-lightbox-img>
                <figcaption data-lightbox-caption></figcaption>
            </figure>
            <button type="button" class="lightbox__nav lightbox__nav--next" data-lightbox-next aria-label="Foto berikutnya"><x-sewa.icon name="chevron" :size="26" /></button>
        </dialog>
    @endif
</x-layouts.app>