@props(['items', 'active', 'route'])

<nav class="tabs" aria-label="Bagian data">
    @foreach ($items as $key => [$label, $count])
        <a class="tab @if ($key === $active) is-active @endif" href="{{ route($route, ['tab' => $key]) }}" @if ($key === $active) aria-current="page" @endif>
            {{ $label }}
            <span class="tab__count">{{ number_format($count, 0, ',', '.') }}</span>
        </a>
    @endforeach
</nav>
