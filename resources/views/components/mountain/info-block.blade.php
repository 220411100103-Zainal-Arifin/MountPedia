@props(['title'])

<section {{ $attributes->merge(['class' => 'py-6 first:pt-0 last:pb-0']) }}>
    <h3 class="mb-4 text-[11px] font-medium uppercase tracking-[0.2em] text-pine-700">{{ $title }}</h3>
    {{ $slot }}
</section>
