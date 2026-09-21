<footer class="border-t border-gray-100 dark:border-white/10 mt-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-8">
            <div class="col-span-2 sm:col-span-1">
                <x-trenakt-logo />
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                    Earn by completing tasks, or promote your business to real people in Nigeria and Ghana.
                </p>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Platform</p>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <li><a href="#how-it-works" class="hover:text-trenakt-primary transition">How it works</a></li>
                    <li><a href="#for-earners" class="hover:text-trenakt-primary transition">For earners</a></li>
                    <li><a href="#for-businesses" class="hover:text-trenakt-primary transition">For businesses</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Legal</p>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <li><a href="{{ route('terms') }}" class="hover:text-trenakt-primary transition">Terms of service</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-trenakt-primary transition">Privacy policy</a></li>
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Account</p>
                <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <li><a href="{{ route('login') }}" wire:navigate class="hover:text-trenakt-primary transition">Log in</a></li>
                    <li><a href="{{ route('register') }}" wire:navigate class="hover:text-trenakt-primary transition">Create account</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-gray-100 dark:border-white/10 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-400">&copy; {{ now()->year }} Trenakt. All rights reserved.</p>
            <p class="text-xs text-gray-400">Built for Nigeria and Ghana.</p>
        </div>
    </div>
</footer>