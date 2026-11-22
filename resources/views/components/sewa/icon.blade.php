@props(['name', 'size' => 18])
@php
    $paths = [
        'whatsapp' => '<path d="M20 11.5a8 8 0 01-11.9 7L4 20l1.5-4A8 8 0 1120 11.5z"/><path d="M9.2 8.6c.2-.4.5-.4.8-.4.2 0 .4.2.5.4l.6 1.4c.1.2 0 .4-.1.6l-.5.6c.6 1.2 1.6 2.1 2.8 2.7l.7-.7c.2-.2.4-.2.6-.1l1.4.7c.2.1.3.3.3.5 0 .5-.4 1.3-1.2 1.5-.7.2-2 0-3.5-1-1.4-.9-2.6-2.3-3-3.5-.3-.9-.1-1.6.2-2.1z"/>',
        'directions' => '<path d="M12 3l9 9-9 9-9-9 9-9z"/><path d="M9 14v-2.5A1.5 1.5 0 0110.5 10H15m0 0l-2-2m2 2l-2 2"/>',
        'pin' => '<path d="M12 21s7-5.6 7-11a7 7 0 10-14 0c0 5.4 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
        'store' => '<path d="M3 9l1.5-5h15L21 9"/><path d="M3 9a3 3 0 006 0 3 3 0 006 0 3 3 0 006 0"/><path d="M5 12v8h14v-8M10 20v-5h4v5"/>',
        'image' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="10" r="1.6"/><path d="M4 18l5-5 4 4 3-3 4 4"/>',
        'gear' => '<path d="M3 19l6-10 4 6 3-4 5 8H3z"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chevron' => '<path d="M9 6l6 6-6 6"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'verified' => '<path d="M12 3l2.4 1.8 3-.2.9 2.9 2.4 1.8-1 2.8 1 2.8-2.4 1.8-.9 2.9-3-.2L12 21l-2.4-1.8-3 .2-.9-2.9L3.3 14.7l1-2.8-1-2.8 2.4-1.8.9-2.9 3 .2L12 3z"/><path d="M8.8 12.2l2.2 2.2 4.2-4.4"/>',
    ];
@endphp
<svg class="icon" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? '' !!}</svg>
