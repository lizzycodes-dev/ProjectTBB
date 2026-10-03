<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .app-layout {
            display: flex;
            min-height: 100vh;
            background: #f3e4d2;
        }

        .app-content {
            flex: 1;
            min-width: 0;
            min-height: 100vh;
            overflow-y: auto;
            background: #f8f1e8;
        }

        .page-header {
            background: #f8f1e8;
            border-bottom: 1px solid #d8c2aa;
        }

        .page-header-inner {
            padding: 20px 30px;
        }
    </style>
</head>

<body class="font-sans antialiased">

    <div class="app-layout">

        @include('layouts.navigation')

        <div class="app-content">

            @isset($header)
            <header class="page-header">
                <div class="page-header-inner">
                    {{ $header }}
                </div>
            </header>
            @endisset

            <main>
                {{ $slot }}
            </main>

        </div>

    </div>

</body>

</html>