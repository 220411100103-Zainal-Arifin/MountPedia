@props(['paginator', 'label' => 'data'])

@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $pages = collect([1, $last, $current - 1, $current, $current + 1])
        ->filter(fn ($p) => $p >= 1 && $p <= $last)->unique()->sort()->values();
@endphp

<div class="panel__foot">
    <p class="count">
        @if ($paginator->total() > 0)
            Menampilkan <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong>
            dari <strong>{{ number_format($paginator->total(), 0, ',', '.') }}</strong> {{ $label }}
        @else
            Tidak ada data
        @endif
    </p>

    @if ($paginator->hasPages())
        <nav class="pager" aria-label="Halaman {{ $label }}">
            @if ($paginator->onFirstPage())
                <span class="is-disabled" aria-disabled="true" aria-label="Halaman sebelumnya"><x-admin.icon name="chevron-down" class="icon icon--sm rot-l" /></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"><x-admin.icon name="chevron-down" class="icon icon--sm rot-l" /></a>
            @endif

            @foreach ($pages as $p)
                @if (! $loop->first && $p - $pages[$loop->index - 1] > 1)
                    <span class="is-gap" aria-hidden="true">…</span>
                @endif
                @if ($p === $current)
                    <span class="is-current" aria-current="page">{{ $p }}</span>
                @else
                    <a href="{{ $paginator->url($p) }}" aria-label="Halaman {{ $p }}">{{ $p }}</a>
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"><x-admin.icon name="chevron-down" class="icon icon--sm rot-r" /></a>
            @else
                <span class="is-disabled" aria-disabled="true" aria-label="Halaman berikutnya"><x-admin.icon name="chevron-down" class="icon icon--sm rot-r" /></span>
            @endif
        </nav>
    @endif
</div>
