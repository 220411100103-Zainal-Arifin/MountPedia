{{--
    Kartu gunung portrait (dipakai carousel rekomendasi; bisa dipakai ulang di halaman lain).
    $mountain : model Mountain dengan relasi region; kolom current_status opsional (scope withCurrentStatus)
--}}
@props(['mountain'])

@php
    $photo = filled($mountain->image_url) && ! str_contains($mountain->image_url, 'placehold.co') ? $mountain->image_url : null;
    $place = Str::replaceFirst('Kabupaten ', '', $mountain->region->regency).', '.$mountain->region->province;
@endphp

<a href="{{ url('/gunung/'.$mountain->slug) }}" {{ $attributes->merge(['class' => 'group relative isolate block aspect-[4/5] overflow-hidden rounded-3xl bg-pine-900 text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-pine-700']) }}>
    @if ($photo)
        <img src="{{ $photo }}" alt="" loading="lazy" decoding="async" class="absolute inset-0 -z-20 size-full object-cover transition duration-700 group-hover:scale-105">
    @else
        <span data-scene="{{ $mountain->slug }}" aria-hidden="true" class="absolute inset-0 -z-20 bg-cover transition duration-700 group-hover:scale-105"></span>
    @endif
    <span class="absolute inset-0 -z-10 bg-gradient-to-t from-pine-950/85 via-transparent to-pine-950/25"></span>

    <x-mountain.status-badge :status="$mountain->current_status ?? null" tone="dark" class="absolute left-4 top-4" />

    <span class="absolute inset-x-5 bottom-5 grid gap-2">
        <span class="font-display text-3xl uppercase leading-none tracking-[0.02em]">{{ $mountain->name }}</span>
        <span class="flex items-center justify-between gap-3 text-[13px]">
            <span class="min-w-0 truncate opacity-90">{{ $place }}</span>
            <span class="shrink-0 font-medium tabular-nums">{{ number_format($mountain->elevation_mdpl, 0, ',', '.') }} mdpl</span>
        </span>
    </span>
</a>
