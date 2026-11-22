@props(['status' => null, 'tone' => 'light'])

@php
    $key = $status ?? '';
    $label = ['buka' => 'Dibuka', 'dibatasi' => 'Dibatasi', 'tutup' => 'Ditutup'][$key] ?? 'Status belum ada';
    $dot = ['buka' => 'bg-emerald-400', 'dibatasi' => 'bg-gold', 'tutup' => 'bg-red-400'][$key] ?? 'bg-slate-400';
    $tones = [
        'light' => 'bg-pine-100 text-pine-900',
        'dark' => 'bg-black/35 text-white backdrop-blur-md',
        'glass' => 'bg-white/15 text-white ring-1 ring-white/25 backdrop-blur-md',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-[11px] font-medium uppercase tracking-[0.08em] '.($tones[$tone] ?? $tones['light'])]) }}>
    <i class="size-2 rounded-full {{ $dot }}"></i>{{ $label }}
</span>
