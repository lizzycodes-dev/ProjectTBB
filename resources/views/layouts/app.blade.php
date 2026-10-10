<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'The Brewing Bar') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">

    @if (request()->is('pos*'))

    {{-- ============================================================
             POS SHELL — no .app-content, no top bar
             POS sidebar is fixed at 240px, .pos-main offsets by 240px.
           ============================================================ --}}
    <div class="pos-shell">
        @include('layouts.pos-navigation')

        <main class="pos-main">
            {{ $slot }}
        </main>
    </div>

    @else

    {{-- ============================================================
             DEFAULT APP SHELL (all other pages)
           ============================================================ --}}
    <div class="app-layout">

        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu">
            <svg viewBox="0 0 24 24">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        @include('layouts.navigation')

        <div class="app-content">

            <x-top-bar />

            @isset($header)
            <header class="page-header">
                <div class="page-header-inner">
                    {{ $header }}
                </div>
            </header>
            @endisset

            <main class="page-content">
                {{ $slot }}
            </main>

        </div>

    </div>

    @endif

</body>

</html>