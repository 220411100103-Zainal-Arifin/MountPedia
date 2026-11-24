@props([
    'title' => null,
    'description' => 'Cari gunung, jalur pendakian, status buka/tutup, tarif simaksi, dan persewaan perlengkapan.',
    'overlay' => false,   
    'footer' => true,
    'assets' => [],       
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' | Info Gunung' : 'Info Gunung' }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="icon" type="image/png" href="{{ asset('images/MountPedia.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(array_merge(['resources/css/layout.css', 'resources/js/layout.js'], $assets))
    {{ $head ?? '' }}
</head>
<body>
    <x-site-nav :overlay="$overlay" />

    {{ $slot }}

    @if ($footer)
        <x-site-footer />
    @endif

    {{ $scripts ?? '' }}
</body>
</html>