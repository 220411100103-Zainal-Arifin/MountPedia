@props(['id', 'eyebrow' => null, 'title' => null])

<section id="{{ $id }}" aria-labelledby="{{ $id }}-title" data-spy-section {{ $attributes->merge(['class' => 'scroll-mt-24']) }}>
    @if ($eyebrow || $title)
        <header class="mb-8 lg:mb-12">
            @if ($eyebrow)
                <p class="flex items-center gap-3 text-xs font-medium uppercase tracking-[0.24em] text-pine-700 before:h-0.5 before:w-7 before:bg-pine-700">{{ $eyebrow }}</p>
            @endif
            @if ($title)
                <h2 id="{{ $id }}-title" class="mt-4 max-w-[18ch] text-balance font-display text-5xl uppercase leading-[0.95] tracking-[0.01em] text-pine-900 lg:text-6xl">{{ $title }}</h2>
            @endif
        </header>
    @endif

    {{ $slot }}
</section>
