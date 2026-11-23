@props(['tone' => 'gray'])

<span {{ $attributes->class(['badge', 'badge--'.$tone]) }}>{{ $slot }}</span>
