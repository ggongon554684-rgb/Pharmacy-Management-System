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
    </head>
    <body class="font-sans text-gray-900 antialiased" style="margin:0;">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-gray-100" style="min-height:100vh; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:2rem 1rem; background:#f3f4f6;">
            <div class="w-full sm:max-w-md">
                <div class="text-center mb-4">
                    <a href="/" class="inline-flex items-center justify-center">
                        <x-application-logo class="w-12 h-12 fill-current text-gray-600" />
                    </a>
                </div>
            </div>

            <div class="w-full sm:max-w-md px-6 py-5 bg-white shadow-md overflow-hidden sm:rounded-lg" style="width:100%; max-width:28rem; background:#fff; border-radius:.5rem; box-shadow:0 10px 25px rgba(0,0,0,.08);">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
