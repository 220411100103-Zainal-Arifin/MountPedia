
@props(['trail', 'summit' => null, 'status' => null, 'index' => 0])

@php
    $num = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, ',', '.'), '0'), ',');
    $gain = ($summit && $trail->start_elevation_mdpl) ? $summit - $trail->start_elevation_mdpl : null;

    $stats = array_filter([
        ['Jarak', $trail->distance_km !== null ? $num($trail->distance_km).' km' : null],
        ['Naik', $trail->duration_up_hours !== null ? $num($trail->duration_up_hours).' jam' : null],
        ['Turun', $trail->duration_down_hours !== null ? $num($trail->duration_down_hours).' jam' : null],
        ['Selisih tinggi', $gain ? '+'.number_format($gain, 0, ',', '.').' m' : null],
    ], fn ($s) => $s[1] !== null);
@endphp

<article {{ $attributes->merge(['class' => 'group -mx-4 rounded-3xl px-4 py-8 transition-colors duration-300 hover:bg-white sm:-mx-6 sm:px-6']) }}>
    <div class="grid gap-x-8 gap-y-5 md:grid-cols-[3.25rem_minmax(0,1fr)_auto]">
        <p class="font-display text-5xl leading-none text-pine-200 transition-colors duration-300 group-hover:text-pine-600" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</p>

        <div class="min-w-0">
            <h3 class="font-display text-4xl uppercase leading-none tracking-[0.01em] text-pine-900">{{ $trail->name }}</h3>

            <p class="mt-3 text-sm text-ink-soft">
                @if ($trail->basecamp_name)Basecamp {{ $trail->basecamp_name }}@endif
                @if ($trail->basecamp_name && $trail->start_elevation_mdpl) · @endif
                @if ($trail->start_elevation_mdpl)mulai {{ number_format($trail->start_elevation_mdpl, 0, ',', '.') }} mdpl @endif
            </p>

            @if ($trail->description)
                <p class="mt-4 max-w-prose text-[15px] leading-7 text-ink-soft">{{ $trail->description }}</p>
            @endif

            @if (count($stats))
                <dl class="mt-6 grid grid-cols-2 gap-x-8 gap-y-5 sm:grid-cols-4">
                    @foreach ($stats as [$term, $value])
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-[0.16em] text-ink-soft">{{ $term }}</dt>
                            <dd class="mt-1 text-lg font-medium tabular-nums text-pine-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            @endif

            @if ($trail->campsites->isNotEmpty())
                <div class="mt-6">
                    <p class="text-[11px] font-medium uppercase tracking-[0.16em] text-ink-soft">Area camp di jalur ini</p>
                    <ul class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-pine-900">
                        @foreach ($trail->campsites as $camp)
                            <li class="flex items-center gap-2">
                                <i class="size-1.5 rounded-full {{ $camp->has_water_source ? 'bg-pine-600' : 'bg-pine-300' }}"></i>
                                {{ $camp->name }}
                                @if ($camp->elevation_mdpl)<span class="tabular-nums text-ink-soft">{{ number_format($camp->elevation_mdpl, 0, ',', '.') }} m</span>@endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-start gap-x-4 gap-y-3 md:flex-col md:items-end">
            <x-mountain.difficulty :level="$trail->difficulty" />
            @if ($status)
                <div class="md:text-right">
                    <x-mountain.status-badge :status="$status->status" />
                    @if ($status->reason)
                        <p class="mt-2 max-w-[16rem] text-xs leading-5 text-ink-soft">{{ $status->reason }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</article>
