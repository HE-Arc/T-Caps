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
        <!-- Barre de navigation latérale -->
        <aside class="fixed h-full w-20 bg-gray-800 text-white flex flex-col border-r border-black">
            @include('layouts.navigation')
        </aside>

        <!-- Contenu principal avec `ml-20` pour laisser la place à l'aside -->
        <main class="flex-1 ml-20 p-6 bg-white background-app relative">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
