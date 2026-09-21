<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Console — Trenakt' }}</title>
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
<body class="bg-trenakt-dark text-white antialiased min-h-screen flex items-center justify-center px-4"
    style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.06) 1px, transparent 0); background-size: 24px 24px;">

    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <div class="w-14 h-14 rounded-xl bg-white/5 border border-white/10 text-trenakt-accent flex items-center justify-center mb-4">
                <x-icon name="shield" class="w-7 h-7" />
            </div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-trenakt-accent mb-1">Restricted access</p>
            <h1 class="text-lg font-bold">Trenakt Admin Console</h1>
        </div>

        <div class="bg-trenakt-surface-dark border border-white/10 rounded-xl shadow-2xl p-6 sm:p-8">
            {{ $slot }}
        </div>

        <p class="text-center text-[11px] text-white/30 mt-6">
            Authorized personnel only. All access is logged.
        </p>
    </div>

    @livewireScripts
</body>
</html>