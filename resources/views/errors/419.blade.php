@php
    $isAdmin = str_starts_with(request()->getHost(), 'admin.');
    $themeKey = $isAdmin ? 'trenakt-admin-theme' : 'trenakt-theme';
    $defaultTheme = $isAdmin ? 'dark' : 'light';

    $backUrl = $isAdmin
        ? (auth()->check() ? route('admin.dashboard') : route('admin.login'))
        : (auth()->check() ? route('dashboard') : route('login'));

    $backLabel = auth()->check() ? 'Back to dashboard' : 'Log in again';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page expired &middot; Trenakt</title>
    <script>
        (function () {
            var theme = localStorage.getItem('{{ $themeKey }}') || '{{ $defaultTheme }}';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="icon" type="image/png" href="{{ asset('images/Favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-trenakt-bg dark:bg-trenakt-dark text-trenakt-dark dark:text-white antialiased min-h-screen flex items-center justify-center px-6">
    <div class="text-center max-w-sm">
        <div class="w-14 h-14 rounded-full bg-trenakt-warning-light text-trenakt-warning flex items-center justify-center mx-auto mb-5">
            <x-icon name="alert-triangle" class="w-6 h-6" />
        </div>
        <h1 class="text-xl font-semibold mb-2">Your session timed out</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
            This page was open a little too long, so for your security we ended the session. Please try again.
        </p>
        <div class="flex items-center justify-center gap-3">
            <button type="button" onclick="window.location.reload()"
                class="inline-flex items-center justify-center border border-gray-200 dark:border-white/10 text-sm font-medium rounded-md px-5 py-2.5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                Refresh page
            </button>
            <a href="{{ $backUrl }}"
                class="inline-flex items-center justify-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition">
                {{ $backLabel }}
            </a>
        </div>
    </div>
</body>
</html>
