<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin' }} &middot; Trenakt</title>
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
<body class="bg-trenakt-bg dark:bg-trenakt-dark text-trenakt-dark dark:text-white antialiased min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <div class="w-12 h-12 rounded-xl bg-trenakt-dark/5 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-trenakt-accent flex items-center justify-center mb-3">
                <x-icon name="shield" class="w-6 h-6" />
            </div>
            <p class="text-sm font-bold text-trenakt-dark dark:text-white">Trenakt</p>
            <p class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-white/40 mt-0.5">Admin console</p>
        </div>

        {{ $slot }}
    </div>

    <x-toast-container />
    @livewireScripts
</body>
</html>
