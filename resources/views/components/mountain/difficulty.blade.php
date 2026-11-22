@props(['level' => null])

@php
    $levels = ['mudah' => 1, 'sedang' => 2, 'sulit' => 3, 'ekstrem' => 4];
    $filled = $levels[$level ?? ''] ?? 0;
@endphp

@if ($filled)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 text-sm font-medium capitalize text-pine-900']) }}>
        <span class="flex items-end gap-0.5" aria-hidden="true">
            @foreach ([1, 2, 3, 4] as $step)
                <i @class(['w-1.5 rounded-sm', 'bg-pine-700' => $step <= $filled, 'bg-pine-200' => $step > $filled]) style="height: {{ 6 + $step * 4 }}px"></i>
            @endforeach
        </span>
        {{ $level }}
    </span>
@endif
