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
    <body class="font-sans antialiased">
        <div class="min-h-screen flex">
            <!-- Barre de navigation latérale avec bordure à droite -->
            <aside class="fixed h-full w-20 bg-gray-800 text-white flex flex-col">
                @include('layouts.navigation')
            </aside>

            <!-- Contenu principal centré -->
            <main class="flex-1 flex items-center justify-center bg-white background-app">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
