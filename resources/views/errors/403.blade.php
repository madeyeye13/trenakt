<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Not permitted &middot; Trenakt</title>
    <meta name="robots" content="noindex, nofollow">
    <script>
        (function () {
            var isAdminHost = window.location.hostname.indexOf('admin.') === 0;
            var theme = localStorage.getItem(isAdminHost ? 'trenakt-admin-theme' : 'trenakt-theme') || (isAdminHost ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="icon" type="image/png" href="{{ asset('images/Favicon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-trenakt-bg dark:bg-trenakt-dark text-trenakt-dark dark:text-white antialiased min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="w-16 h-16 rounded-full bg-trenakt-danger/10 text-trenakt-danger flex items-center justify-center mx-auto mb-6">
            <x-icon name="lock" class="w-7 h-7" />
        </div>

        <h1 class="text-2xl font-bold mb-2">You don't have access to this page</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
            Your role isn't permitted to view this. If you think that's wrong, ask a super admin to update your role's permissions.
        </p>

        @auth
            @if (auth()->user()->roles->whereNotIn('name', ['participant', 'business'])->isNotEmpty())
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center justify-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition">
                    Back to dashboard
                </a>
            @else
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center justify-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition">
                    Back to dashboard
                </a>
            @endif
        @else
            <a href="{{ route('login') }}"
                class="inline-flex items-center justify-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition">
                Sign in
            </a>
        @endauth
    </div>
</body>
</html>
