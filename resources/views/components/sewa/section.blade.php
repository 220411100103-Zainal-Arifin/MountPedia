@props(['id', 'eyebrow' => null, 'title'])
<section id="{{ $id }}" class="sec" aria-labelledby="{{ $id }}-title" data-section>
    <header class="sec__head">
        @if ($eyebrow)
            <p class="sec__eyebrow">{{ $eyebrow }}</p>
        @endif
        <h2 class="sec__title" id="{{ $id }}-title">{{ $title }}</h2>
    </header>
    {{ $slot }}
</section>
