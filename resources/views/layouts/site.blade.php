<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Job Collector') }} — @yield('title', 'آگهی‌ها')</title>

    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme');
                if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
        function toggleTheme() {
            var dark = document.documentElement.classList.toggle('dark');
            try {
                localStorage.setItem('theme', dark ? 'dark' : 'light');
            } catch (e) {}
        }
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,600,700,800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @livewireStyles
</head>
<body class="flex min-h-screen flex-col bg-background font-sans text-foreground antialiased">
    <header class="sticky top-0 z-40 border-b bg-background/85 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-sm">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </span>
                <span class="text-base font-bold tracking-tight">جاب‌کالکتور</span>
            </a>
            <nav class="flex items-center gap-2 text-sm font-medium">
                <a href="{{ url('/') }}" class="rounded-md px-3 py-2 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground">آگهی‌ها</a>
                <x-ui.button size="sm" href="/admin">پنل مدیریت</x-ui.button>
                <x-ui.button size="icon" variant="ghost" onclick="toggleTheme()" title="تغییر حالت روشن / تیره">
                    <svg class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                    <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 12 21.75a9.753 9.753 0 0 0 9.752-6.748Z" />
                    </svg>
                </x-ui.button>
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6">
        @yield('content')
    </main>

    <footer class="border-t bg-card">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-3 sm:px-6">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </span>
                    <span class="text-base font-bold tracking-tight">جاب‌کالکتور</span>
                </div>
                <p class="mt-3 max-w-xs text-sm leading-6 text-muted-foreground">
                    آگهی‌های تازه‌ی استخدام که به‌صورت خودکار از جاب‌بردهای ایرانی جمع‌آوری و یکجا نمایش داده می‌شوند.
                </p>
            </div>
            <div>
                <h4 class="mb-3 text-sm font-semibold">دسترسی سریع</h4>
                <ul class="space-y-2 text-sm text-muted-foreground">
                    <li><a href="{{ url('/') }}" class="transition-colors hover:text-foreground">همه آگهی‌ها</a></li>
                    <li><a href="{{ url('/?sort=newest') }}" class="transition-colors hover:text-foreground">تازه‌ترین آگهی‌ها</a></li>
                    <li><a href="{{ url('/admin') }}" class="transition-colors hover:text-foreground">پنل مدیریت</a></li>
                </ul>
            </div>
            <div>
                <h4 class="mb-3 text-sm font-semibold">منابع</h4>
                <ul class="space-y-2 text-sm text-muted-foreground">
                    @forelse ($footerSources ?? [] as $source)
                        <li>
                            <a href="{{ url('/?platform=' . $source->id) }}" class="transition-colors hover:text-foreground">
                                آگهی‌های {{ $source->title }}
                            </a>
                        </li>
                    @empty
                        <li>به‌زودی</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <x-ui.separator />
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-muted-foreground sm:flex-row sm:px-6">
            <p>© {{ date('Y') }} جاب‌کالکتور — همه حقوق محفوظ است.</p>
            <p>ساخته‌شده با لاراول و لایوویر</p>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
