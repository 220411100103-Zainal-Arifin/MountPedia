@props(['label', 'tone' => 'paper'])

@php
    $tones = [
        'paper' => 'bg-white text-ink',
        'mint' => 'bg-pine-100 text-pine-900',
        'forest' => 'bg-pine-800 text-white',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col justify-between gap-8 rounded-3xl p-5 sm:p-6 '.($tones[$tone] ?? $tones['paper'])]) }}>
    <p class="text-[11px] font-medium uppercase tracking-[0.18em] opacity-60">{{ $label }}</p>
    <div>{{ $slot }}</div>
</div>
