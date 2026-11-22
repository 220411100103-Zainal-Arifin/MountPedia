@props(['icon' => 'info', 'title', 'text' => null])
<div {{ $attributes->class(['empty-state']) }}>
    <span class="empty-state__icon"><x-sewa.icon :name="$icon" :size="22" /></span>
    <div class="empty-state__body">
        <p class="empty-state__title">{{ $title }}</p>
        @if ($text)
            <p class="empty-state__text">{{ $text }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
