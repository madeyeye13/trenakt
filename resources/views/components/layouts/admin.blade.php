<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} — Trenakt</title>
    <meta name="robots" content="noindex, nofollow">
    <script>
        (function () {
            var theme = localStorage.getItem('trenakt-admin-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="icon" type="image/png" href="{{ asset('images/Favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-trenakt-bg dark:bg-trenakt-dark text-trenakt-dark dark:text-white antialiased min-h-screen">
    <div class="flex">
        <x-admin-sidebar class="w-64 shrink-0" />

        <div class="flex-1 min-w-0 flex flex-col">
            <header class="bg-white dark:bg-trenakt-surface-dark border-b border-gray-200 dark:border-white/10 sticky top-0 z-40">
                <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-trenakt-accent">Platform admin</p>

                    <div class="flex items-center gap-4">
                        <x-admin-theme-toggle />

                        <div class="w-9 h-9 rounded-full bg-trenakt-accent text-white flex items-center justify-center text-sm font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-toast-container />
    @livewireScripts
</body>
</html>