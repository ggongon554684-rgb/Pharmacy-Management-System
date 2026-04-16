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
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>
    <body>
        <div class="d-flex min-vh-100 app-shell">
            @include('layouts.navigation')

            <div class="d-flex flex-column flex-grow-1 app-main">
                @if (isset($header))
                    <header class="app-header shadow-sm">
                        <div class="container-fluid py-3 px-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 page-header">
                                <div class="w-100">{{ $header }}</div>
                                <small class="text-muted">Welcome back, {{ Auth::user()->name }}</small>
                            </div>
                        </div>
                    </header>
                @endif

                <main class="flex-grow-1">
                    {{ $slot }}
                </main>

                <footer class="app-footer py-2 px-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-1">
                        <span>&copy; {{ now()->year }} Pharmacy Management System</span>
                        <span>Built for daily pharmacy operations</span>
                    </div>
                </footer>
            </div>
        </div>
    </body>
</html>
