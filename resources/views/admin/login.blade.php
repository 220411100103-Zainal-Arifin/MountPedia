<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk | MountPedia Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/MountPedia.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin-login.js'])
</head>
<body class="admin-body login-page">
    
    <div class="login-bg" data-login-bg data-slug="{{ $bgSlug }}" @if ($bgPhoto) data-image="{{ $bgPhoto }}" @endif aria-hidden="true"></div>
    <div class="login-shade" aria-hidden="true"></div>

    <main class="login">
        <div class="glass">
            <div class="glass__brand">
                <img src="{{ asset('images/MountPedia.png') }}" alt="">
                <span>Mount<b>Pedia</b></span>
                <em>Admin</em>
            </div>

            <h1 class="glass__title">Masuk</h1>
            <p class="glass__text">Selamat datang kembali, silakan masuk ke akun admin Anda.</p>

            <form method="POST" action="{{ route('admin.login.store') }}" class="glass__form" novalidate>
                @csrf
                <input type="hidden" name="bg" value="{{ $bgSlug }}">

                <div class="pill">
                    <label class="sr-only" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Email"
                           autocomplete="username" required autofocus @class(['is-invalid' => $errors->has('email')])>
                    <x-admin.icon name="user" class="pill__icon" />
                </div>

                <div class="pill">
                    <label class="sr-only" for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Password"
                           autocomplete="current-password" required data-pw>
                    <button class="pill__btn" type="button" aria-label="Tampilkan password" aria-pressed="false" data-pw-toggle>
                        <x-admin.icon name="eye" class="i-on" />
                        <x-admin.icon name="eye-off" class="i-off" />
                    </button>
                </div>

                @error('email')
                    <p class="glass__error" role="alert">{{ $message }}</p>
                @enderror

                <label class="check">
                    <input type="checkbox" name="remember" value="1">
                    <span class="check__box"><x-admin.icon name="check" /></span>
                    <span>Ingat saya</span>
                </label>

                <button class="glass__submit" type="submit">Masuk</button>
            </form>

            <a class="glass__back" href="{{ url('/') }}">&larr; Kembali ke website</a>
        </div>
    </main>

    <p class="login-caption"><x-admin.icon name="mountain" /> {{ $bgName }}</p>
</body>
</html>