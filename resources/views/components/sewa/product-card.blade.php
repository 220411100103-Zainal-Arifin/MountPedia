@props(['item'])
<article
    class="product"
    data-item
    data-name="{{ mb_strtolower($item->name) }}"
    data-cat="{{ $item->category_key }}"
>
    <div class="product__media @unless ($item->image) product__media--empty @endunless">
        @if ($item->image)
            <img src="{{ $item->image }}" alt="{{ $item->name }}" loading="lazy" decoding="async">
        @else
            <x-sewa.icon name="gear" :size="44" />
        @endif
    </div>

    <div class="product__body">
        <p class="product__cat">{{ $item->category }}</p>
        <h3 class="product__name">
            @if ($item->href)
                <a href="{{ $item->href }}">{{ $item->name }}</a>
            @else
                {{ $item->name }}
            @endif
        </h3>

        <p class="product__price">
            <strong>Rp {{ number_format($item->price, 0, ',', '.') }}</strong><span>/ hari</span>
        </p>

        <p @class(['product__avail', 'is-known' => filled($item->stock_note)])>
            @if (filled($item->stock_note))
                <x-sewa.icon name="check" :size="14" /> {{ $item->stock_note }}
            @else
                Ketersediaan: tanyakan pemilik
            @endif
        </p>

        @if (filled($item->notes))
            <p class="product__note">{{ $item->notes }}</p>
        @endif

        @if ($item->wa_url)
            <a class="btn btn--line btn--sm product__cta" href="{{ $item->wa_url }}" target="_blank" rel="noopener">
                <x-sewa.icon name="whatsapp" :size="16" /> Tanyakan barang ini
            </a>
        @endif
    </div>
</article>
