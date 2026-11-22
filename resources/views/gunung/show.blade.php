@php
    $place = Str::replaceFirst('Kabupaten ', '', $mountain->region->regency).', '.$mountain->region->province;
    $elev = number_format($mountain->elevation_mdpl, 0, ',', '.');
    $hasPhotos = $mountain->photos->isNotEmpty();

    
    $dateId = fn ($d, $fmt = 'j F Y') => $d?->locale('id')->translatedFormat($fmt);
    $rupiah = fn ($v) => $v === null ? '—' : 'Rp '.number_format($v, 0, ',', '.');
    $dms = fn ($v) => number_format((float) $v, 4, ',', '.').'°';

    $statusMeta = [
        'buka' => ['Dibuka', 'bg-emerald-400'],
        'dibatasi' => ['Dibatasi', 'bg-gold'],
        'tutup' => ['Ditutup', 'bg-red-400'],
    ];
    [$statusLabel, $statusDot] = $statusMeta[$status?->status ?? ''] ?? ['Belum ada laporan', 'bg-slate-400'];

    $coords = ($mountain->latitude !== null && $mountain->longitude !== null)
        ? $dms($mountain->latitude).', '.$dms($mountain->longitude)
        : null;

    $facts = array_filter([
        ['Provinsi', $mountain->region->province],
        ['Kabupaten / kota', Str::replaceFirst('Kabupaten ', '', $mountain->region->regency)],
        ['Pengelola', $mountain->managing_authority],
        ['Koordinat puncak', $coords],
    ], fn ($f) => filled($f[1]));

    
    $sections = array_values(array_filter([
        ['ringkasan', 'Ringkasan', true],
        ['jalur', 'Jalur', true],
        ['peta', 'Peta', count($mapPoints) > 0],
        ['galeri', 'Galeri', $hasPhotos],
        ['serupa', 'Gunung lain', $related->isNotEmpty()],
    ], fn ($s) => $s[2]));

    $description = Str::limit(strip_tags((string) $mountain->description), 155)
        ?: 'Informasi ketinggian, jalur, status pendakian, tarif simaksi, dan peta '.$mountain->name.'.';
@endphp

<x-layouts.app
    :title="$mountain->name"
    :description="$description"
    :overlay="true"
    :assets="['resources/css/app.css', 'resources/js/mountain-detail.js']"
>
    <x-slot:head>
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    </x-slot:head>

    
    <header class="relative isolate flex h-[80svh] min-h-[34rem] items-end overflow-hidden bg-pine-950 text-white lg:h-[88svh]">
        @if ($heroPhoto)
            <img src="{{ $heroPhoto }}" alt="Panorama {{ $mountain->name }}" fetchpriority="high" class="absolute inset-0 -z-20 size-full object-cover">
        @else
            <div data-scene="{{ $mountain->slug }}" role="img" aria-label="Ilustrasi {{ $mountain->name }}" class="absolute inset-0 -z-20 bg-cover"></div>
        @endif
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-pine-950 via-pine-950/35 to-pine-950/25"></div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-pine-950/70 via-pine-950/10 to-transparent"></div>

        <div class="container pb-36 pt-32 lg:pb-44">
            <nav aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-white/70">
                    <li><a href="{{ url('/') }}" class="transition hover:text-white">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('mountains.index') }}" class="transition hover:text-white">Gunung</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="text-white">{{ $mountain->name }}</li>
                </ol>
            </nav>

            <p class="mt-8 flex items-center gap-3 text-xs font-medium uppercase tracking-[0.24em] text-white/85 before:h-0.5 before:w-7 before:bg-gold">{{ $place }}</p>
            <h1 class="mt-4 max-w-[12ch] text-balance font-display text-[clamp(4.25rem,12vw,10.5rem)] uppercase leading-[0.88] tracking-[0.005em]">{{ $mountain->name }}</h1>

            <div class="mt-9 flex flex-wrap items-center gap-x-7 gap-y-4">
                <p class="font-display text-4xl leading-none tracking-wide">{{ $elev }} <span class="text-xl text-white/70">mdpl</span></p>
                <x-mountain.status-badge :status="$status?->status" tone="glass" />
                <div class="flex flex-wrap gap-3">
                    <a href="#jalur" class="rounded-full bg-white px-6 py-3 text-xs font-medium uppercase tracking-[0.12em] text-pine-900 transition hover:bg-pine-100">Lihat jalur</a>
                    @if (count($mapPoints))
                        <a href="#peta" class="rounded-full border border-white/50 px-6 py-3 text-xs font-medium uppercase tracking-[0.12em] text-white transition hover:bg-white/10">Buka peta</a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <div class="bg-paper text-ink">
        
        <div class="container relative z-10 -mt-28 lg:-mt-32">
            <div class="grid grid-cols-2 gap-3 rounded-[2rem] bg-paper p-3 shadow-[0_40px_80px_-40px_rgba(8,26,19,0.5)] lg:grid-cols-6 lg:grid-rows-2">
                <x-mountain.bento-tile label="Ketinggian puncak" tone="forest" class="col-span-2 lg:row-span-2">
                    <p class="font-display text-[clamp(4.5rem,9vw,8rem)] leading-[0.85] tracking-wide">{{ $elev }}</p>
                    <p class="mt-1 font-display text-3xl text-white/70">mdpl</p>
                    <div class="mt-8 h-1.5 overflow-hidden rounded-full bg-white/15" role="img" aria-label="{{ $stats['elevation_pct'] }} persen dari gunung tertinggi di MountPedia">
                        <div class="h-full rounded-full bg-gold" style="width: {{ $stats['elevation_pct'] }}%"></div>
                    </div>
                    <p class="mt-3 text-sm leading-6 text-white/70">Peringkat {{ $stats['rank'] }} dari {{ $stats['total'] }} gunung di MountPedia berdasarkan ketinggian.</p>
                </x-mountain.bento-tile>

                <x-mountain.bento-tile label="Status pendakian" class="lg:col-span-2">
                    <p class="flex items-center gap-3 font-display text-4xl uppercase leading-none text-pine-900 sm:text-5xl">
                        <i class="size-3 shrink-0 rounded-full {{ $statusDot }}"></i>{{ $statusLabel }}
                    </p>
                    <p class="mt-3 text-sm leading-6 text-ink-soft">
                        @if ($status)
                            Sejak {{ $dateId($status->starts_on) }}
                        @else
                            Belum ada laporan; tanyakan ke pengelola.
                        @endif
                    </p>
                </x-mountain.bento-tile>

                <x-mountain.bento-tile label="Jalur pendakian" tone="mint" class="lg:col-span-2">
                    @if ($stats['trail_count'])
                        <p class="font-display text-4xl uppercase leading-none sm:text-5xl">{{ $stats['trail_count'] }} jalur</p>
                        <p class="mt-3 text-sm leading-6 opacity-75">
                            {{ $stats['difficulty'] ?? 'Tingkat kesulitan belum ada' }}@if ($stats['camp_count']) · {{ $stats['camp_count'] }} area camp @endif
                        </p>
                    @else
                        <p class="text-lg opacity-60">Belum ada data jalur</p>
                    @endif
                </x-mountain.bento-tile>

                <x-mountain.bento-tile label="Estimasi waktu naik" tone="mint" class="lg:col-span-2">
                    @if ($stats['climb'])
                        <p class="font-display text-4xl uppercase leading-none sm:text-5xl">{{ $stats['climb'] }}</p>
                        <p class="mt-3 text-sm leading-6 opacity-75">Dari basecamp ke puncak, tergantung jalur.</p>
                    @else
                        <p class="text-lg opacity-60">Belum tersedia</p>
                    @endif
                </x-mountain.bento-tile>

                <x-mountain.bento-tile label="Lokasi" class="lg:col-span-2">
                    <p class="font-display text-4xl uppercase leading-none text-pine-900 sm:text-5xl">{{ Str::replaceFirst('Kabupaten ', '', $mountain->region->regency) }}</p>
                    <p class="mt-3 text-sm leading-6 text-ink-soft">
                        {{ $mountain->region->province }}@if ($coords) <span class="block tabular-nums">{{ $coords }}</span>@endif
                    </p>
                </x-mountain.bento-tile>
            </div>
        </div>

        
        <div data-nav-sentinel class="mt-10 h-px"></div>
        <div data-section-nav data-stuck="false" class="group sticky top-0 z-30 border-b border-line bg-paper/85 backdrop-blur-xl">
            <div class="container flex items-center gap-6">
                <p class="hidden shrink-0 font-display text-2xl uppercase leading-none tracking-wide text-pine-900 opacity-0 transition duration-300 group-data-[stuck=true]:opacity-100 md:block">{{ $mountain->name }}</p>
                <nav aria-label="Bagian halaman" data-section-nav-scroll class="flex min-w-0 flex-1 gap-1 overflow-x-auto [scrollbar-width:none] md:justify-end">
                    @foreach ($sections as [$id, $label])
                        <a
                            href="#{{ $id }}"
                            aria-current="false"
                            class="relative whitespace-nowrap px-4 py-4 text-[13px] font-medium text-ink-soft transition-colors hover:text-pine-900 after:absolute after:inset-x-4 after:bottom-0 after:h-0.5 after:origin-left after:scale-x-0 after:bg-pine-700 after:transition-transform after:duration-300 aria-[current=true]:text-pine-900 aria-[current=true]:after:scale-x-100"
                        >{{ $label }}</a>
                    @endforeach
                </nav>
            </div>
        </div>

        
        <div class="container grid gap-14 py-14 lg:grid-cols-[minmax(0,1fr)_21rem] lg:gap-16 lg:py-20 xl:grid-cols-[minmax(0,1fr)_23rem]">

            
            <aside aria-label="Informasi penting" class="lg:order-2">
                <div class="lg:sticky lg:top-[4.75rem] lg:max-h-[calc(100svh-6rem)] lg:overflow-y-auto lg:overscroll-contain lg:[scrollbar-width:thin]">
                    <div class="divide-y divide-line rounded-[2rem] border border-line bg-white p-6 sm:p-7">

                        <x-mountain.info-block title="Status pendakian">
                            <p class="flex items-center gap-3 font-display text-4xl uppercase leading-none text-pine-900">
                                <i class="size-3 shrink-0 rounded-full {{ $statusDot }}"></i>{{ $statusLabel }}
                            </p>

                            @if ($status)
                                @if ($status->reason)
                                    <p class="mt-3 text-sm leading-6 text-ink-soft">{{ $status->reason }}</p>
                                @endif
                                <p class="mt-3 text-xs leading-5 text-ink-soft">
                                    Berlaku sejak {{ $dateId($status->starts_on) }}@if ($status->ends_on) hingga {{ $dateId($status->ends_on) }}@endif.
                                </p>
                                <p class="mt-1 text-xs leading-5 text-ink-soft">
                                    Sumber:
                                    @if ($status->source_url)
                                        <a href="{{ $status->source_url }}" target="_blank" rel="noopener" class="underline underline-offset-2 hover:text-pine-800">{{ $status->source_name ?: 'tautan sumber' }}</a>
                                    @else
                                        {{ $status->source_name ?: 'tidak dicantumkan' }}
                                    @endif
                                    · dilaporkan {{ $status->reported_at->locale('id')->diffForHumans() }}
                                </p>
                            @else
                                <p class="mt-3 text-sm leading-6 text-ink-soft">Belum ada laporan status untuk gunung ini. Hubungi pengelola sebelum berangkat.</p>
                            @endif

                            @if ($trailStatuses->isNotEmpty())
                                <ul class="mt-5 space-y-2 border-t border-dashed border-line pt-4 text-sm">
                                    @foreach ($mountain->trails as $t)
                                        @if (isset($trailStatuses[$t->id]))
                                            <li class="flex items-center justify-between gap-3">
                                                <span class="min-w-0 truncate text-pine-900">{{ $t->name }}</span>
                                                <x-mountain.status-badge :status="$trailStatuses[$t->id]->status" />
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </x-mountain.info-block>

                        @if ($permit)
                            <x-mountain.info-block title="Tarif simaksi">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="text-[11px] uppercase tracking-[0.14em] text-ink-soft">
                                            <th scope="col" class="pb-2 font-medium"><span class="sr-only">Jenis</span></th>
                                            <th scope="col" class="pb-2 text-right font-medium">WNI</th>
                                            <th scope="col" class="pb-2 text-right font-medium">WNA</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        @foreach ($permit['rows'] as $i => $row)
                                            @if ($i === 0 || $permit['rows'][$i - 1]['trip'] !== $row['trip'])
                                                <tr>
                                                    <th scope="rowgroup" colspan="3" class="pb-1 pt-5 text-[13px] font-medium text-pine-900">
                                                        {{ $row['trip'] }} <span class="font-normal text-ink-soft">· {{ $row['unit'] }}</span>
                                                    </th>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td class="py-2 pr-2 text-ink-soft">{{ $row['day'] }}</td>
                                                <td class="py-2 text-right tabular-nums text-pine-900">{{ $rupiah($row['wni']) }}</td>
                                                <td class="py-2 text-right tabular-nums text-pine-900">{{ $rupiah($row['wna']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <div class="mt-4 space-y-1 text-xs leading-5 text-ink-soft">
                                    @if ($permit['valid_until'])
                                        <p>Berlaku hingga {{ $dateId($permit['valid_until']) }}.</p>
                                    @endif
                                    @foreach ($permit['notes'] as $note)
                                        <p>{{ $note }}</p>
                                    @endforeach
                                    @if ($permit['source_url'])
                                        <p><a href="{{ $permit['source_url'] }}" target="_blank" rel="noopener" class="underline underline-offset-2 hover:text-pine-800">Sumber tarif</a></p>
                                    @endif
                                </div>
                            </x-mountain.info-block>
                        @endif

                        @if ($weather->isNotEmpty())
                            <x-mountain.info-block title="Prakiraan cuaca">
                                <div class="divide-y divide-line">
                                    @foreach (['basecamp' => 'Basecamp', 'puncak' => 'Puncak'] as $point => $pointLabel)
                                        @if (isset($weather[$point]))
                                            @php $w = $weather[$point]; @endphp
                                            <div class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
                                                <div class="min-w-0">
                                                    <p class="text-[11px] font-medium uppercase tracking-[0.16em] text-ink-soft">{{ $pointLabel }}</p>
                                                    <p class="mt-1 text-sm text-pine-900">{{ $w->condition ?: 'Kondisi belum ada' }}</p>
                                                    <p class="mt-1 text-xs leading-5 text-ink-soft">
                                                        @if ($w->rain_probability_pct !== null)Hujan {{ $w->rain_probability_pct }}%@endif
                                                        @if ($w->wind_kmh !== null) · angin {{ round($w->wind_kmh) }} km/jam @endif
                                                    </p>
                                                    <p class="mt-1 text-xs text-ink-soft">Perkiraan {{ $dateId($w->forecast_at, 'j M, H.i') }}</p>
                                                </div>
                                                @if ($w->temp_c !== null)
                                                    <p class="font-display text-5xl leading-none text-pine-900">{{ round($w->temp_c) }}°</p>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                @php $latest = $weather->first(); @endphp
                                <p class="mt-4 text-xs leading-5 text-ink-soft">
                                    Sumber: {{ $latest->source ?: 'tidak dicantumkan' }} · diperbarui {{ $latest->fetched_at->locale('id')->diffForHumans() }}
                                </p>
                            </x-mountain.info-block>
                        @endif

                        <x-mountain.info-block title="Siapkan pendakian">
                            <ul class="-my-1 divide-y divide-line text-sm font-medium text-pine-900">
                                <li><a href="{{ url('/perlengkapan?gunung='.$mountain->slug) }}" class="group flex items-center justify-between py-3 transition hover:text-pine-600">Rekomendasi perlengkapan <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></a></li>
                                <li><a href="{{ url('/sewa?gunung='.$mountain->slug) }}" class="group flex items-center justify-between py-3 transition hover:text-pine-600">Sewa alat di sekitar gunung <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span></a></li>
                            </ul>
                        </x-mountain.info-block>
                    </div>
                </div>
            </aside>

            
            <div class="min-w-0 space-y-24 lg:order-1 lg:space-y-32">

                
                <x-mountain.section id="ringkasan" eyebrow="Ringkasan" :title="'Mengenal '.$mountain->name">
                    <div class="grid gap-10 md:grid-cols-[12rem_minmax(0,1fr)] md:gap-14">
                        @if (count($facts))
                            <dl class="grid grid-cols-2 gap-x-6 gap-y-6 text-sm md:block md:space-y-6 md:border-r md:border-line md:pr-8">
                                @foreach ($facts as [$term, $value])
                                    <div>
                                        <dt class="text-[11px] font-medium uppercase tracking-[0.16em] text-ink-soft">{{ $term }}</dt>
                                        <dd class="mt-1 font-medium leading-6 text-pine-900">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif

                        <div class="min-w-0 {{ count($facts) ? '' : 'md:col-span-2' }}">
                            @if ($overview['lead'])
                                <p class="text-balance text-2xl font-light leading-[1.45] text-pine-900 lg:text-[1.75rem]">{{ $overview['lead'] }}</p>
                                @foreach ($overview['body'] as $paragraph)
                                    <p class="mt-6 max-w-[62ch] text-base leading-8 text-ink-soft">{{ $paragraph }}</p>
                                @endforeach
                            @else
                                <p class="text-base leading-8 text-ink-soft">Deskripsi untuk gunung ini belum ditulis.</p>
                            @endif
                        </div>
                    </div>
                </x-mountain.section>

                
                <x-mountain.section id="jalur" eyebrow="Jalur pendakian" title="Pilih jalurmu">
                    @if ($mountain->trails->isEmpty())
                        <p class="rounded-2xl bg-pine-100 p-6 text-sm leading-6 text-pine-900">Belum ada data jalur untuk gunung ini.</p>
                    @else
                        <p class="mb-8 max-w-[60ch] text-[15px] leading-7 text-ink-soft">
                            Jarak dan waktu adalah perkiraan. Cuaca, beban bawaan, dan kebugaran memengaruhi waktu tempuh; mulailah lebih awal dari yang kamu kira perlu.
                        </p>

                        <div class="divide-y divide-line border-y border-line">
                            @foreach ($mountain->trails as $trail)
                                <x-mountain.trail-row
                                    :trail="$trail"
                                    :index="$loop->index"
                                    :summit="$mountain->elevation_mdpl"
                                    :status="$trailStatuses[$trail->id] ?? null"
                                />
                            @endforeach
                        </div>

                        @if ($sharedCamps->isNotEmpty())
                            <div class="mt-10">
                                <h3 class="text-[11px] font-medium uppercase tracking-[0.16em] text-ink-soft">Area camp yang tidak terikat satu jalur</h3>
                                <ul class="mt-3 grid gap-x-8 gap-y-2 text-sm text-pine-900 sm:grid-cols-2">
                                    @foreach ($sharedCamps as $camp)
                                        <li class="flex items-center gap-2">
                                            <i class="size-1.5 rounded-full {{ $camp->has_water_source ? 'bg-pine-600' : 'bg-pine-300' }}"></i>
                                            {{ $camp->name }}
                                            @if ($camp->elevation_mdpl)<span class="tabular-nums text-ink-soft">{{ number_format($camp->elevation_mdpl, 0, ',', '.') }} m</span>@endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <p class="mt-8 flex items-center gap-4 text-xs text-ink-soft">
                            <span class="flex items-center gap-2"><i class="size-1.5 rounded-full bg-pine-600"></i>ada sumber air</span>
                            <span class="flex items-center gap-2"><i class="size-1.5 rounded-full bg-pine-300"></i>tanpa sumber air</span>
                        </p>
                    @endif
                </x-mountain.section>

                
                @if (count($mapPoints))
                    <x-mountain.section id="peta" eyebrow="Peta" title="Titik penting di lapangan">
                        <p class="mb-8 max-w-[60ch] text-[15px] leading-7 text-ink-soft">
                            Puncak, basecamp tiap jalur, dan area camp. Data lintasan (GPX) belum tersedia, jadi peta menampilkan titik, bukan rute.
                        </p>

                        <div class="relative isolate overflow-hidden rounded-[2rem] bg-pine-100 ring-1 ring-line">
                            <div data-map role="region" aria-label="Peta lokasi {{ $mountain->name }}" class="h-[26rem] w-full lg:h-[34rem]">
                                <div data-map-loading class="grid size-full place-items-center text-sm text-pine-700">Memuat peta…</div>
                            </div>
                        </div>

                        <ul class="mt-6 grid gap-x-10 sm:grid-cols-2">
                            @foreach ($mapPoints as $point)
                                <li>
                                    <button type="button" data-map-focus="{{ $point['key'] }}" class="group flex w-full items-center gap-4 border-b border-line py-3.5 text-left transition hover:border-pine-700">
                                        <span @class([
                                            'grid size-8 shrink-0 place-items-center rounded-full text-xs font-bold',
                                            'bg-pine-900 text-white' => $point['type'] === 'summit',
                                            'bg-pine-100 text-pine-800 ring-1 ring-pine-200' => $point['type'] === 'basecamp',
                                            'bg-gold text-pine-950' => $point['type'] === 'camp',
                                        ])>
                                            @if ($point['type'] === 'summit')
                                                <svg viewBox="0 0 24 24" class="size-4" fill="currentColor" aria-hidden="true"><path d="M12 4 2 20h20L12 4zm0 5.2 5.4 9.3H6.6L12 9.2z"/></svg>
                                            @elseif ($point['type'] === 'basecamp')
                                                {{ $point['order'] }}
                                            @else
                                                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round" aria-hidden="true"><path d="M3 20 12 5l9 15H3z"/><path d="M12 20v-7"/></svg>
                                            @endif
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-medium text-pine-900">{{ $point['name'] }}</span>
                                            <span class="block truncate text-xs text-ink-soft">{{ $point['label'] }}@if ($point['detail']) · {{ $point['detail'] }}@endif</span>
                                        </span>
                                        <span aria-hidden="true" class="text-ink-soft transition group-hover:translate-x-1 group-hover:text-pine-700">→</span>
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <p class="mt-5 text-xs leading-6 text-ink-soft">
                            Posisi titik bersifat perkiraan; selalu ikuti petunjuk petugas dan rambu di lapangan. Di layar sentuh, geser peta dengan dua jari.
                        </p>

                        <script type="application/json" id="map-data">@json($mapPoints)</script>
                    </x-mountain.section>
                @endif

                
                @if ($hasPhotos)
                    <x-mountain.section id="galeri" eyebrow="Galeri" title="Dari lapangan">
                        <ul class="columns-2 gap-3 sm:columns-3 [&>li]:mb-3">
                            @foreach ($mountain->photos as $photo)
                                <li class="break-inside-avoid">
                                    <button
                                        type="button"
                                        data-lightbox-item
                                        data-full="{{ $photo->src }}"
                                        data-alt="{{ $photo->caption ?: 'Foto '.$mountain->name }}"
                                        data-caption="{{ $photo->caption }}"
                                        data-credit="{{ $photo->credit }}"
                                        class="group relative block w-full overflow-hidden rounded-2xl bg-pine-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pine-700"
                                        aria-label="Perbesar foto{{ $photo->caption ? ': '.$photo->caption : '' }}"
                                    >
                                        <img
                                            src="{{ $photo->src }}"
                                            alt="{{ $photo->caption ?: 'Foto '.$mountain->name }}"
                                            @if ($photo->width && $photo->height) width="{{ $photo->width }}" height="{{ $photo->height }}" @endif
                                            loading="lazy"
                                            decoding="async"
                                            class="h-auto w-full transition duration-700 group-hover:scale-105"
                                        >
                                        @if ($photo->credit)
                                            <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent px-3 pb-2 pt-8 text-left text-[11px] text-white opacity-0 transition group-hover:opacity-100 group-focus-visible:opacity-100">Foto: {{ $photo->credit }}</span>
                                        @endif
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <dialog data-lightbox aria-label="Pratinjau foto" class="m-auto max-h-[94svh] w-[min(96vw,72rem)] overflow-visible bg-transparent p-0 text-white backdrop:bg-pine-950/90 backdrop:backdrop-blur-sm">
                            <div class="relative">
                                <img data-lb-img alt="" class="mx-auto max-h-[78svh] w-auto max-w-full rounded-2xl object-contain">
                                <div class="mt-4 flex items-start justify-between gap-6 text-sm">
                                    <p data-lb-caption class="min-w-0 text-white/80"></p>
                                    <p data-lb-count class="shrink-0 tabular-nums text-white/60"></p>
                                </div>
                                <button type="button" data-lb-close aria-label="Tutup" class="absolute -top-3 right-0 grid size-10 -translate-y-full place-items-center rounded-full bg-white/10 text-xl transition hover:bg-white/20">✕</button>
                                <button type="button" data-lb-prev aria-label="Foto sebelumnya" class="absolute left-2 top-[38%] grid size-11 place-items-center rounded-full bg-black/40 text-xl backdrop-blur transition hover:bg-black/60">←</button>
                                <button type="button" data-lb-next aria-label="Foto berikutnya" class="absolute right-2 top-[38%] grid size-11 place-items-center rounded-full bg-black/40 text-xl backdrop-blur transition hover:bg-black/60">→</button>
                            </div>
                        </dialog>
                    </x-mountain.section>
                @endif
            </div>
        </div>

        
        @if ($related->isNotEmpty())
            <section id="serupa" data-spy-section data-carousel aria-labelledby="serupa-title" class="scroll-mt-24 border-t border-line bg-white py-16 lg:py-24">
                <div class="container flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="flex items-center gap-3 text-xs font-medium uppercase tracking-[0.24em] text-pine-700 before:h-0.5 before:w-7 before:bg-pine-700">Jelajahi lagi</p>
                        <h2 id="serupa-title" class="mt-4 text-balance font-display text-5xl uppercase leading-[0.95] tracking-[0.01em] text-pine-900 lg:text-6xl">Gunung lain untukmu</h2>
                        <p class="mt-4 max-w-[52ch] text-sm leading-6 text-ink-soft">Diurutkan dari provinsi yang sama, lalu ketinggian yang paling mendekati {{ $mountain->name }}.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" data-carousel-prev aria-label="Geser ke kiri" class="grid size-12 place-items-center rounded-full border border-pine-800 text-pine-800 transition hover:bg-pine-800 hover:text-white disabled:pointer-events-none disabled:opacity-30">←</button>
                        <button type="button" data-carousel-next aria-label="Geser ke kanan" class="grid size-12 place-items-center rounded-full border border-pine-800 text-pine-800 transition hover:bg-pine-800 hover:text-white disabled:pointer-events-none disabled:opacity-30">→</button>
                    </div>
                </div>

                <div
                    data-carousel-track
                    class="mt-10 flex snap-x snap-mandatory gap-4 overflow-x-auto pb-4 pl-[max(var(--pad-x),calc((100vw_-_1200px)/2))] pr-[var(--pad-x)] [scroll-padding-left:max(var(--pad-x),calc((100vw_-_1200px)/2))] [scrollbar-width:none]"
                >
                    @foreach ($related as $other)
                        <x-mountain.card :mountain="$other" class="w-[17.5rem] shrink-0 snap-start sm:w-[20rem]" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
