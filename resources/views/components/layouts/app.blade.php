@php
    $hour = now()->hour;
    $greeting = match (true) {
        $hour < 12 => 'Good morning',
        $hour < 17 => 'Good afternoon',
        default => 'Good evening',
    };
    $firstName = explode(' ', auth()->user()->name)[0];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Trenakt' }}</title>
    <script>
        (function () {
            var theme = localStorage.getItem('trenakt-theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <link rel="icon" type="image/png" href="{{ asset('images/Favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Favicon01.png') }}">
    @laravelPWA
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans bg-trenakt-bg text-trenakt-dark dark:bg-trenakt-dark dark:text-white antialiased min-h-screen">
    <div class="flex">
        <x-sidebar-nav class="hidden lg:flex w-64 shrink-0" />

        <div class="flex-1 min-w-0 flex flex-col">
            <header class="bg-white dark:bg-trenakt-surface-dark border-b border-gray-200 dark:border-white/10 sticky top-0 z-40">
                <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="lg:hidden">
                            <x-trenakt-logo />
                        </div>
                        <p class="hidden lg:block text-sm text-gray-500 dark:text-gray-400">
                            {{ $greeting }}, <span class="font-medium text-trenakt-dark dark:text-white">{{ $firstName }}</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        @livewire('mode-switcher')

                        <x-theme-toggle />

                        <div class="w-9 h-9 rounded-full bg-trenakt-primary text-white flex items-center justify-center text-sm font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 pb-24 lg:pb-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-bottom-nav class="lg:hidden" />
    <x-toast-container />

    @livewireScripts
</body>
</html>