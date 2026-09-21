<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Trenakt' }}</title>

    <link rel="icon" type="image/png" href="{{ asset('images/Favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Favicon01.png') }}">
    @laravelPWA
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-trenakt-bg text-trenakt-dark antialiased min-h-screen flex">
    <div class="hidden lg:flex lg:w-1/2 bg-trenakt-primary flex-col justify-between p-12 text-white">
        <x-trenakt-logo on-dark />
        <div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight mb-4">
                Where earning meets<br>
                <span class="text-trenakt-accent-light">promoting</span>.
            </h1>
            <p class="text-white/85 text-base sm:text-lg leading-relaxed max-w-sm">
                Complete tasks and get paid, or put your business in front of the right people. All on one platform.
            </p>
        </div>
        <p class="text-white/50 text-sm">&copy; {{ now()->year }} Trenakt</p>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex justify-center lg:hidden">
                <x-trenakt-logo />
            </div>
            {{ $slot }}
        </div>
    </div>

    <x-install-prompt />
    <x-toast-container />
    @livewireScripts
</body>
</html>