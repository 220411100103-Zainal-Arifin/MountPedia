@props(['text' => null])

<div class="page-head">
    <p class="page-head__text">{{ $text }}</p>
    @if ($slot->isNotEmpty())
        <div class="page-head__actions">{{ $slot }}</div>
    @endif
</div>
