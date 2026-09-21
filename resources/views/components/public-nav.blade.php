<header x-data="{ open: false }" class="sticky top-0 z-50 bg-white/80 dark:bg-trenakt-dark/80 backdrop-blur border-b border-gray-100 dark:border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <x-trenakt-logo />

        <nav class="hidden lg:flex items-center gap-8">
            <a href="#how-it-works" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-trenakt-primary transition">How it works</a>
            <a href="#for-earners" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-trenakt-primary transition">For earners</a>
            <a href="#for-businesses" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-trenakt-primary transition">For businesses</a>
            <a href="#faq" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-trenakt-primary transition">FAQ</a>
        </nav>

        <div class="hidden lg:flex items-center gap-4">
            <x-theme-toggle />
            <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-trenakt-primary transition">Log in</a>
            <a href="{{ route('register') }}" wire:navigate
                class="btn-fill inline-flex items-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-5 py-2.5 transition">
                <span class="relative">Get started</span>
            </a>
        </div>

        <button type="button" @click="open = !open" class="lg:hidden text-gray-600 dark:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6" x-show="!open">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <x-icon name="x" class="w-6 h-6" x-show="open" x-cloak />
        </button>
    </div>

    <div x-show="open" x-cloak x-transition class="lg:hidden border-t border-gray-100 dark:border-white/10 px-4 sm:px-6 py-4 space-y-3 bg-white dark:bg-trenakt-dark">
        <a href="#how-it-works" @click="open = false" class="block text-sm font-medium text-gray-600 dark:text-gray-300">How it works</a>
        <a href="#for-earners" @click="open = false" class="block text-sm font-medium text-gray-600 dark:text-gray-300">For earners</a>
        <a href="#for-businesses" @click="open = false" class="block text-sm font-medium text-gray-600 dark:text-gray-300">For businesses</a>
        <a href="#faq" @click="open = false" class="block text-sm font-medium text-gray-600 dark:text-gray-300">FAQ</a>
        <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-white/10">
            <a href="{{ route('login') }}" wire:navigate class="text-sm font-medium text-gray-600 dark:text-gray-300">Log in</a>
            <x-theme-toggle />
        </div>
        <a href="{{ route('register') }}" wire:navigate class="block text-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-5 py-2.5">Get started</a>
    </div>
</header>