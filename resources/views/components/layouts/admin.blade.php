<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} &middot; Trenakt</title>
    <meta name="robots" content="noindex, nofollow">
    <script>
        (function () {
            function applyTheme() {
                var theme = localStorage.getItem('trenakt-admin-theme') || 'dark';
                document.documentElement.setAttribute('data-theme', theme);
            }
            applyTheme();
            document.addEventListener('livewire:navigated', applyTheme);
        })();
    </script>
    <link rel="icon" type="image/png" href="{{ asset('images/Favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-trenakt-bg dark:bg-trenakt-dark text-trenakt-dark dark:text-white antialiased min-h-screen">
    <div x-data="{ sidebarOpen: false }" class="flex">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

        <x-admin-sidebar class="w-64 shrink-0" />

        <div class="flex-1 min-w-0 flex flex-col">
            <header class="bg-white dark:bg-trenakt-surface-dark border-b border-gray-200 dark:border-white/10 sticky top-0 z-30">
                <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="sidebarOpen = true" class="lg:hidden text-gray-500 dark:text-white/60 hover:text-trenakt-dark dark:hover:text-white transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                            </svg>
                        </button>

                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-trenakt-accent">Platform admin</p>
                    </div>

                    <div class="flex items-center gap-4">
                        @livewire('notification-bell')

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
