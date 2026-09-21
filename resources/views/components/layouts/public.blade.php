<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Trenakt — Earn by completing tasks, or promote your business in Nigeria and Ghana' }}</title>
    <meta name="description" content="{{ $description ?? 'Trenakt connects people in Nigeria and Ghana who want to earn money completing simple tasks with businesses that need real testers, feedback, and market research.' }}">
    <meta property="og:title" content="{{ $title ?? 'Trenakt — Earn or promote, all on one platform' }}">
    <meta property="og:description" content="{{ $description ?? 'Complete tasks and get paid, or put your business in front of real people in Nigeria and Ghana.' }}">
    <meta property="og:image" content="{{ asset('images/Trenaktlogo.png') }}">
    <meta property="og:type" content="website">

    <script>
        (function () {
            var theme = localStorage.getItem('trenakt-theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>

    <link rel="icon" type="image/png" href="{{ asset('images/Favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Favicon.png') }}">
    @laravelPWA
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-trenakt-bg dark:bg-trenakt-dark text-trenakt-dark dark:text-white antialiased min-h-screen">
    <x-public-nav />

    <main>
        {{ $slot }}
    </main>

    <x-public-footer />
    <x-toast-container />
    @livewireScripts
</body>
</html>