{{--
    Satu skenario rekomendasi gear (mis. "Camping 2 malam").
    $scenario : ['key', 'label', 'summary', 'groups' => [['category', 'items' => [[name, priority, qty, note, specific, url]]]]]
    url terisi hanya bila barang tersedia di daftar Perlengkapan (tautan pencarian sesuai nama barang)
--}}
@props(['scenario', 'mountainName' => null, 'active' => false])

@php
    $priorityLabel = ['wajib' => 'Wajib', 'disarankan' => 'Disarankan', 'opsional' => 'Opsional'];
    $priorityClass = [
        'wajib' => 'bg-pine-800 text-white',
        'disarankan' => 'bg-pine-100 text-pine-900',
        'opsional' => 'text-ink-soft ring-1 ring-inset ring-line',
    ];
@endphp

<div
    id="gear-panel-{{ $scenario['key'] }}"
    role="tabpanel"
    aria-labelledby="gear-tab-{{ $scenario['key'] }}"
    data-gear-panel="{{ $scenario['key'] }}"
    @if (! $active) hidden @endif
    {{ $attributes }}
>
    <p class="text-sm text-ink-soft">{{ $scenario['summary'] }}</p>

    <div class="mt-8 space-y-10">
        @foreach ($scenario['groups'] as $group)
            <section>
                <h3 class="flex items-baseline justify-between border-b border-pine-800 pb-2 text-[11px] font-medium uppercase tracking-[0.2em] text-pine-800">
                    {{ $group['category'] }}
                    <span class="tabular-nums text-ink-soft">{{ count($group['items']) }}</span>
                </h3>

                <ul class="divide-y divide-line">
                    @foreach ($group['items'] as $item)
                        <li class="grid gap-x-6 gap-y-2 py-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-start">
                            <div class="min-w-0">
                                <p class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                                    <span class="text-[15px] font-medium text-pine-900">{{ $item['name'] }}</span>
                                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-medium uppercase tracking-[0.1em] {{ $priorityClass[$item['priority']] ?? $priorityClass['opsional'] }}">{{ $priorityLabel[$item['priority']] ?? $item['priority'] }}</span>
                                </p>

                                @if ($item['qty'])
                                    <p class="mt-1 text-sm font-medium text-pine-800">{{ $item['qty'] }}</p>
                                @endif

                                @if ($item['note'])
                                    <p @class(['mt-1 text-sm leading-6 text-ink-soft', 'border-l-2 border-gold pl-3 text-pine-900' => $item['specific']])>
                                        @if ($item['specific'])<span class="block text-[11px] font-medium uppercase tracking-[0.14em] text-pine-700">Khusus {{ $mountainName ?? 'gunung ini' }}</span>@endif
                                        {{ $item['note'] }}
                                    </p>
                                @endif
                            </div>

                            @if ($item['url'])
                                <a href="{{ $item['url'] }}" class="group inline-flex items-center gap-2 text-sm font-medium text-pine-800 transition hover:text-pine-600 sm:justify-end sm:pt-0.5">
                                    Cari di Perlengkapan <span aria-hidden="true" class="transition group-hover:translate-x-1">→</span>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
</div>
