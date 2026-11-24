@props([
    'title' => 'Dashboard',
    'section' => 'Pages',
])

@php
    $admin = auth()->user();
    $name = $admin->name ?? 'Administrator';
    $initials = collect(preg_split('/\s+/', trim($name)))->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    // [label, ikon, url, aktif?]
    $menu = [
        'Main Menu' => [
            ['Dashboard', 'layout-dashboard', route('admin.dashboard'), request()->routeIs('admin.dashboard')],
        ],
        'Master Data' => [
            ['Gunung & Jalur', 'mountain', route('admin.mountains.index'), request()->routeIs('admin.mountains.*', 'admin.trails.*')],
            ['Gear & Kategori', 'backpack', route('admin.gear.index'), request()->routeIs('admin.gear.*')],
            ['Tempat Sewa', 'store', route('admin.shops.index'), request()->routeIs('admin.shops.*')],
            ['Barang Sewa', 'package', route('admin.items.index'), request()->routeIs('admin.items.*')],
        ],
        'Content' => [
            ['Artikel & Informasi', 'newspaper', '#', false],
            ['Galeri', 'image', '#', false],
        ],
        'Management' => [
            ['Pengguna & Admin', 'users', '#', false],
            ['Pengaturan Website', 'settings', '#', false],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} | MountPedia Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/MountPedia.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin-body">
<div class="admin" data-admin>
    <aside class="sb" id="sidebar" aria-label="Navigasi admin">
        <a class="sb__brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('images/MountPedia.png') }}" alt="" class="sb__logo">
            <span class="sb__brand-text">
                <span class="sb__name">Mount<b>Pedia</b></span>
                <span class="sb__tag">Admin Panel</span>
            </span>
        </a>

        <nav class="sb__nav">
            @foreach ($menu as $group => $items)
                <div class="sb__section" style="--n: {{ count($items) }}">
                <p class="sb__group">{{ $group }}</p>
                <ul>
                    @foreach ($items as [$label, $icon, $href, $active])
                        <li>
                            <a class="sb__link @if ($active) is-active @endif" href="{{ $href }}" title="{{ $label }}" @if ($active) aria-current="page" @endif>
                                <x-admin.icon :name="$icon" />
                                <span class="sb__label">{{ $label }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                </div>
            @endforeach
        </nav>
    </aside>
    <div class="sb-overlay" data-sidebar-close></div>

    <div class="main">
        <header class="topbar">
            <button class="icon-btn" type="button" aria-label="Ciutkan atau buka sidebar" data-sidebar-toggle>
                <x-admin.icon name="panel-left" />
            </button>
            <div class="topbar__title">
                <p class="crumb">{{ $section }} <span>/</span> {{ $title }}</p>
                <h1>{{ $title }}</h1>
            </div>

            <div class="topbar__right">
                <button class="icon-btn has-dot" type="button" aria-label="Notifikasi"><x-admin.icon name="bell" /></button>
                <a class="btn btn--ghost" href="{{ url('/') }}" target="_blank" rel="noopener">
                    <x-admin.icon name="external-link" /> <span class="hide-sm">View Website</span>
                </a>

                <div class="dropdown" data-dropdown>
                    <button class="profile" type="button" aria-haspopup="menu" aria-expanded="false" data-dropdown-toggle>
                        <span class="avatar avatar--sm">{{ $initials }}</span>
                        <span class="profile__name hide-sm">{{ $name }}</span>
                        <x-admin.icon name="chevron-down" class="icon icon--sm hide-sm" />
                    </button>
                    <div class="dropdown__menu" role="menu" hidden>
                        <div class="dropdown__head">
                            <strong>{{ $name }}</strong>
                            <span>Super Admin</span>
                        </div>
                        <a role="menuitem" href="#"><x-admin.icon name="user" /> Profil Saya</a>
                        <a role="menuitem" href="#"><x-admin.icon name="settings" /> Pengaturan</a>
                        <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                            @csrf
                            <button role="menuitem" type="submit"><x-admin.icon name="log-out" /> Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="content">
            @if (session('success'))
                <div class="flash flash--success" role="status" data-flash>{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="flash flash--error" role="alert">{{ session('error') }}</div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

    <dialog class="modal" id="confirm-delete" aria-labelledby="cd-title">
        <form method="POST" class="modal__box" data-delete-form>
            @csrf
            @method('DELETE')
            <span class="modal__icon"><x-admin.icon name="trash-2" /></span>
            <h2 class="modal__title" id="cd-title">Hapus <span data-delete-name></span>?</h2>
            <p class="modal__text" data-delete-note></p>
            <div class="modal__actions">
                <button class="btn btn--ghost" type="button" data-delete-cancel>Batal</button>
                <button class="btn btn--danger" type="submit">Ya, hapus</button>
            </div>
        </form>
    </dialog>
</body>
</html>