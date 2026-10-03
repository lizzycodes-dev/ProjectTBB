<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'The Brewing Bar') }}
    </title>


    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
        rel="stylesheet" />


    {{-- Scripts --}}
    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])

</head>


<body class="font-sans antialiased">

    <div class="app-layout">

        {{-- Sidebar --}}
        @include('layouts.navigation')


        {{-- Main Application Area --}}
        <div class="app-content">

            {{-- Page Header --}}
            @isset($header)

            <header class="page-header">

                <div class="page-header-inner">
                    {{ $header }}
                </div>

            </header>

            @endisset


            {{-- Page Content --}}
            <main class="page-content">

                {{ $slot }}

            </main>

        </div>

    </div>

</body>

</html>