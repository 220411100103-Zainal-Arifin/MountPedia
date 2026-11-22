@props(['overlay' => false])

@php
    
    $links = [
        ['Beranda', url('/'), '/'],
        ['Gunung', url('/gunung'), 'gunung*'],
        ['Perlengkapan', url('/perlengkapan'), 'perlengkapan*'],
        ['Sewa Alat', url('/sewa'), 'sewa*'],
    ];
@endphp

<header @class(['nav', 'nav--overlay' => $overlay])>
    <a class="nav__brand" href="{{ url('/') }}" aria-label="Info Gunung, beranda">
        <img class="nav__logo" src="{{ asset('images/MountPedia.png') }}" alt="">
        <span class="nav__word"><span class="nav__word-a">Mount</span><span class="nav__word-b">Pedia</span></span>
    </a>

    <nav class="nav__menu" id="site-menu" aria-label="Menu utama">
        <ul class="nav__links">
            @foreach ($links as [$label, $href, $pattern])
                <li><a href="{{ $href }}" @if (request()->is($pattern)) aria-current="page" @endif>{{ $label }}</a></li>
            @endforeach
        </ul>
    </nav>

    <div class="nav__tools">
        <button class="nav__icon nav__toggle" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="site-menu" data-menu-toggle>
            <span></span><span></span><span></span>
        </button>
    </div>
</header>