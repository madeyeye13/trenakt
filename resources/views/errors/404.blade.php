<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found — Trenakt</title>
    <script>
        (function () {
            var theme = localStorage.getItem('trenakt-theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="icon" type="image/png" href="{{ asset('images/Favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-trenakt-bg dark:bg-trenakt-dark text-trenakt-dark dark:text-white antialiased min-h-screen flex items-center justify-center px-6">
    <div class="text-center max-w-sm">
        <p class="text-6xl font-extrabold text-trenakt-primary mb-4">404</p>
        <h1 class="text-xl font-semibold mb-2">Page not found</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
            The page you're looking for doesn't exist or may have moved.
        </p>
        <a href="{{ auth()->check() ? route('dashboard') : '/' }}"
            class="inline-flex items-center justify-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition">
            {{ auth()->check() ? 'Back to dashboard' : 'Back to homepage' }}
        </a>
    </div>
</body>
</html>