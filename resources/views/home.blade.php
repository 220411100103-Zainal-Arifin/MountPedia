<x-layouts.app
    title="MountPedia"
    :overlay="true"
    :footer="false"
    :assets="['resources/css/home.css', 'resources/js/home.js']"
>
    <main class="hero" data-hero aria-roledescription="carousel" aria-label="Gunung pilihan">
        <div class="hero__bg"></div>
        <div class="hero__shade"></div>

        <section class="hero__content" aria-live="polite">
            <span class="hero__dash"></span>
            <p class="hero__place">{{ $first['place'] }}</p>
            <h1 class="hero__title">{{ $first['name'] }}</h1>
            <ul class="hero__meta"></ul>
            <p class="hero__desc">{{ $first['description'] }}</p>
            <div class="hero__actions">
                <button class="btn-save" type="button" aria-label="Simpan gunung ini">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 3h12a1 1 0 011 1v17l-7-4-7 4V4a1 1 0 011-1z"/></svg>
                </button>
                <a class="btn-detail" href="#">Lihat detail</a>
            </div>
        </section>

        <div class="hero__cards"><div class="hero__track"></div></div>

        <div class="hero__controls">
            <button class="arrow" type="button" data-prev aria-label="Gunung sebelumnya">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
            </button>
            <button class="arrow" type="button" data-next aria-label="Gunung berikutnya">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
            </button>
            <span class="hero__line" aria-hidden="true"></span>
            <span class="hero__count" aria-hidden="true">01</span>
        </div>
    </main>

    <script type="application/json" id="hero-data">@json($slides)</script>
</x-layouts.app>