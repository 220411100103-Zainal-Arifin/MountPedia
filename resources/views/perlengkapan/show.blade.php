@php
    $mainPhoto = $photos[0] ?? null;
    $supporting = array_slice($photos, 1, 2); // foto pendukung di bagian deskripsi
    $keySpecs = array_slice($specs, 0, 3);
    $hasText = $paragraphs || $supporting;
    $hasSide = $features || $usage || $usageNotes;
    $hasEditorial = $hasText || $hasSide;
    // susunan menyesuaikan isi: dua kolom hanya bila teks DAN panel samping sama-sama ada
    $editorialMode = $hasText && $hasSide ? 'split' : ($hasText ? 'text' : 'solo');
@endphp
<x-layouts.app
    :title="$item->name"
    :description="$metaDescription"
    :assets="['resources/css/sewa-detail.css', 'resources/css/gear-detail.css', 'resources/js/gear-detail.js']"
>
    <main class="shop gear">
        <div class="container">

            {{-- Hero produk: galeri (±55%) + informasi (±45%) --}}
            <section class="hero" aria-label="Ringkasan barang">
                <div class="hero__gallery">
                    @if ($mainPhoto)
                        <button type="button" class="stage" data-stage aria-label="Perbesar foto">
                            <img
                                src="{{ $mainPhoto['url'] }}"
                                alt="{{ $mainPhoto['caption'] !== '' ? $mainPhoto['caption'] : $item->name }}"
                                decoding="async"
                            >
                            <span class="stage__zoom"><x-sewa.icon name="image" :size="16" /> Perbesar</span>
                        </button>

                        @if (count($photos) > 1)
                            <ul class="thumbs" aria-label="Foto lainnya">
                                @foreach ($photos as $i => $photo)
                                    <li>
                                        <button
                                            type="button"
                                            @class(['thumb', 'is-active' => $i === 0])
                                            data-thumb
                                            data-src="{{ $photo['url'] }}"
                                            data-alt="{{ $photo['caption'] !== '' ? $photo['caption'] : $item->name.', foto '.($i + 1) }}"
                                            aria-label="Tampilkan foto {{ $i + 1 }}"
                                            aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                                        >
                                            <img src="{{ $photo['url'] }}" alt="" loading="lazy" decoding="async">
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @else
                        <div class="stage stage--empty">
                            <x-sewa.icon name="gear" :size="72" />
                            <p>Foto barang belum tersedia</p>
                        </div>
                    @endif
                </div>

                <div class="hero__info">
                    <a class="hero__cat" href="{{ $categoryUrl }}">{{ $item->category_name }} <x-sewa.icon name="chevron" :size="14" /></a>
                    <h1 class="hero__name">{{ $item->name }}</h1>

                    @if ($short !== '')
                        <p class="hero__lead">{{ $short }}</p>
                    @endif

                    @if ($keySpecs)
                        <dl class="quick">
                            @foreach ($keySpecs as $s)
                                <div><dt>{{ $s['label'] }}</dt><dd>{{ $s['value'] }}</dd></div>
                            @endforeach
                        </dl>
                    @endif

                    <div class="hero__price">
                        @if ($minPrice !== null)
                            <p class="hero__from">Harga sewa mulai dari</p>
                            <p class="hero__amount"><strong>Rp {{ number_format($minPrice, 0, ',', '.') }}</strong> <span>/ hari</span></p>
                            <p class="muted">Tersedia di {{ count($shops) }} tempat sewa</p>
                        @else
                            <p class="hero__from">Harga sewa</p>
                            <p class="muted">
                                @if ($item->is_rentable)
                                    Belum ada tempat sewa yang mencantumkan barang ini.
                                @else
                                    Barang ini umumnya tidak disewakan.
                                @endif
                            </p>
                        @endif
                    </div>

                    @if ($shops)
                        <a class="btn btn--primary hero__cta" href="#sewa"><x-sewa.icon name="store" /> Lihat Tempat Sewa</a>
                    @endif
                </div>
            </section>

            <div class="gear__body">

                {{-- Deskripsi: tata letak editorial --}}
                <x-sewa.section id="deskripsi" eyebrow="Tentang barang" title="Deskripsi">
                    @if ($hasEditorial)
                        <div class="editorial editorial--{{ $editorialMode }}">
                            @if ($hasText)
                            <div class="editorial__text">
                                @foreach ($paragraphs as $i => $p)
                                    <p @class(['about__lead' => $i === 0])>{{ trim($p) }}</p>
                                @endforeach

                                @if ($supporting)
                                    <div class="editorial__figs editorial__figs--{{ count($supporting) }}">
                                        @foreach ($supporting as $i => $photo)
                                            <figure>
                                                <img src="{{ $photo['url'] }}" alt="{{ $photo['caption'] !== '' ? $photo['caption'] : $item->name.', foto pendukung '.($i + 1) }}" loading="lazy" decoding="async">
                                                @if ($photo['caption'] !== '')
                                                    <figcaption>{{ $photo['caption'] }}</figcaption>
                                                @endif
                                            </figure>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @endif

                            @if ($hasSide)
                            <div class="editorial__side">
                                @if ($features)
                                    <div class="editorial__block">
                                    <h3 class="h3">Fitur utama</h3>
                                    <ul class="ticks">
                                        @foreach ($features as $f)
                                            <li><x-sewa.icon name="check" :size="16" /> {{ $f }}</li>
                                        @endforeach
                                    </ul>
                                    </div>
                                @endif

                                @if ($usage || $usageNotes)
                                    <div class="editorial__block">
                                    <h3 class="h3">Rekomendasi penggunaan</h3>
                                    @if ($usage)
                                        <ul class="usage">
                                            @foreach ($usage as $u)
                                                <li>
                                                    <span class="usage__trip">{{ $u->trip }}</span>
                                                    <span class="usage__tag" data-priority="{{ $u->priority }}">{{ $u->priority_label }}</span>
                                                    @if ($u->nights || $u->note)
                                                        <span class="usage__note">{{ collect([$u->nights, $u->note])->filter()->implode(' · ') }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if ($usageNotes !== '')
                                        <p class="usage__text">{{ $usageNotes }}</p>
                                    @endif
                                    </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    @else
                        <x-sewa.empty icon="info" title="Deskripsi lengkap belum tersedia" text="Penjelasan, fitur, dan rekomendasi penggunaan barang ini belum ditambahkan." />
                    @endif
                </x-sewa.section>

                {{-- Spesifikasi teknis: hanya tampil bila ada datanya --}}
                @if ($specs)
                    <x-sewa.section id="spesifikasi" eyebrow="Detail" title="Spesifikasi teknis">
                        <table class="specs">
                            <caption class="sr-only">Spesifikasi {{ $item->name }}</caption>
                            <tbody>
                                @foreach ($specs as $s)
                                    <tr><th scope="row">{{ $s['label'] }}</th><td>{{ $s['value'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </x-sewa.section>
                @endif

                {{-- Tempat sewa + perbandingan harga (digabung) --}}
                <x-sewa.section id="sewa" eyebrow="Tempat sewa" title="Tempat sewa yang menyediakan barang ini">
                    @if ($shops)
                        @if (count($shops) > 1)
                            <p class="providers__hint">Diurutkan dari harga sewa terendah per hari.</p>
                        @endif
                        <ul class="providers">
                            @foreach ($shops as $s)
                                <li class="provider">
                                    <a class="provider__thumb" href="{{ $s->url }}" tabindex="-1" aria-hidden="true">
                                        @if ($s->image)
                                            <img src="{{ $s->image }}" alt="" loading="lazy" decoding="async">
                                        @else
                                            <x-sewa.icon name="store" :size="26" />
                                        @endif
                                    </a>

                                    <div class="provider__main">
                                        <h3 class="provider__name">
                                            <a href="{{ $s->url }}">{{ $s->name }}</a>
                                            @if ($s->verified)
                                                <span class="verified" title="Terverifikasi"><x-sewa.icon name="verified" :size="15" /></span>
                                            @endif
                                        </h3>
                                        <p class="provider__loc"><x-sewa.icon name="pin" :size="14" /> {{ filled($s->address) ? $s->address.', ' : '' }}{{ $s->location }}</p>
                                        @if ($s->listed_as)
                                            <p class="provider__note">Di toko ini bernama “{{ $s->listed_as }}”</p>
                                        @endif
                                        @if (filled($s->notes))
                                            <p class="provider__note">{{ $s->notes }}</p>
                                        @endif
                                    </div>

                                    <div class="provider__deal">
                                        <p class="provider__price">
                                            <strong>Rp {{ number_format($s->price, 0, ',', '.') }}</strong><span>/ hari</span>
                                        </p>
                                        @if ($s->cheapest)
                                            <p class="provider__best">Harga terendah</p>
                                        @endif
                                        <p @class(['product__avail', 'is-known' => filled($s->stock_note)])>
                                            @if (filled($s->stock_note))
                                                <x-sewa.icon name="check" :size="14" /> {{ $s->stock_note }}
                                            @else
                                                Ketersediaan: tanyakan toko
                                            @endif
                                        </p>
                                    </div>

                                    <a class="btn btn--line btn--sm provider__go" href="{{ $s->url }}">
                                        Kunjungi Toko <x-sewa.icon name="arrow" :size="15" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <p class="fineprint">
                            <x-sewa.icon name="info" :size="15" />
                            Harga dan ketersediaan dicatat oleh masing-masing toko dan dapat berubah. Konfirmasi lewat WhatsApp di halaman toko.
                        </p>
                    @else
                        <x-sewa.empty icon="store" title="Belum ada tempat sewa untuk barang ini" text="Belum ada toko yang mencantumkan barang ini di katalognya. Lihat semua tempat sewa untuk menanyakan langsung.">
                            <a class="btn btn--line btn--sm" href="{{ url('/sewa') }}">Lihat semua tempat sewa</a>
                        </x-sewa.empty>
                    @endif
                </x-sewa.section>

                {{-- Perlengkapan serupa --}}
                <section class="sec" id="serupa" aria-labelledby="serupa-title">
                    <header class="sec__head sec__head--row">
                        <div>
                            <p class="sec__eyebrow">Jelajahi</p>
                            <h2 class="sec__title" id="serupa-title">Perlengkapan serupa</h2>
                        </div>
                        @if (count($related) > 2)
                            <div class="rail-nav">
                                <button type="button" class="rail-nav__btn rail-nav__btn--prev" data-rail-prev aria-label="Geser ke kiri"><x-sewa.icon name="chevron" :size="18" /></button>
                                <button type="button" class="rail-nav__btn" data-rail-next aria-label="Geser ke kanan"><x-sewa.icon name="chevron" :size="18" /></button>
                            </div>
                        @endif
                    </header>

                    @if ($related)
                        <ul class="rail" data-rail>
                            @foreach ($related as $r)
                                <li>
                                    <a class="rcard" href="{{ url('/perlengkapan/'.$r->slug) }}">
                                        <span @class(['rcard__media', 'rcard__media--empty' => ! $r->image_url])>
                                            @if ($r->image_url)
                                                <img src="{{ $r->image_url }}" alt="" loading="lazy" decoding="async">
                                            @else
                                                <x-sewa.icon name="gear" :size="36" />
                                            @endif
                                        </span>
                                        <span class="rcard__name">{{ $r->name }}</span>
                                        <span class="rcard__price">
                                            @if ($r->min_price)
                                                mulai Rp {{ number_format($r->min_price, 0, ',', '.') }} / hari
                                            @else
                                                <span class="muted">Belum ada tempat sewa</span>
                                            @endif
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <x-sewa.empty icon="gear" title="Belum ada perlengkapan serupa" text="Kategori ini baru memiliki satu barang.">
                            <a class="btn btn--line btn--sm" href="{{ $categoryUrl }}">Lihat kategori {{ $item->category_name }}</a>
                        </x-sewa.empty>
                    @endif
                </section>
            </div>
        </div>
    </main>

    @if ($photos)
        <dialog class="lightbox" data-lightbox data-photos="{{ json_encode($photos) }}" aria-label="Galeri foto {{ $item->name }}">
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