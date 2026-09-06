<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Job Collector') }} — @yield('title', 'Jobs')</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="min-h-screen bg-stone-100 text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ url('/') }}" class="text-xl font-bold tracking-tight">
                💼 {{ config('app.name', 'Job Collector') }}
            </a>
            <nav class="flex items-center gap-3 text-sm">
                <a href="{{ url('/') }}" class="font-medium text-stone-700 hover:text-stone-950">Jobs</a>
                <a href="{{ url('/admin') }}" class="rounded-md bg-amber-500 px-3 py-1.5 font-semibold text-white hover:bg-amber-600">Admin</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
        @yield('content')
    </main>

    <footer class="mx-auto max-w-6xl px-4 pb-8 text-center text-xs text-stone-500 sm:px-6">
        Collected from configured job boards · managed in the admin panel
    </footer>

    @livewireScripts
</body>
</html>
