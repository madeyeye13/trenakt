<x-layouts.public
    title="Trenakt — Earn money completing tasks or grow your business in Nigeria and Ghana"
    description="Join Trenakt to earn money completing simple tasks, or run publicity, product testing, and market research campaigns with real people across Nigeria and Ghana.">

    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-trenakt-primary-light via-trenakt-bg to-trenakt-bg dark:from-trenakt-primary/10 dark:via-trenakt-dark dark:to-trenakt-dark"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 sm:pt-28 sm:pb-32 text-center">
            <div class="inline-flex items-center gap-2 bg-white dark:bg-trenakt-surface-dark border border-gray-100 dark:border-white/10 rounded-full px-4 py-1.5 mb-8 shadow-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-trenakt-primary opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-trenakt-primary"></span>
                </span>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Now live in Nigeria and Ghana</span>
            </div>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.1] max-w-3xl mx-auto">
                Earn money completing tasks. Or find people to
                <span class="text-trenakt-accent">promote</span> your business.
            </h1>

            <p class="text-base sm:text-lg text-gray-600 dark:text-gray-300 max-w-xl mx-auto mt-6 leading-relaxed">
                Trenakt connects everyday people who want to earn with businesses that need testers, feedback, and real market research. One platform, two sides, built for Nigeria and Ghana.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-10">
                <a href="{{ route('register') }}" wire:navigate
                    class="btn-fill w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-trenakt-primary text-white text-sm font-semibold rounded-md px-7 py-3.5 transition">
                    <span class="relative">Start earning</span>
                </a>
                <a href="{{ route('register') }}" wire:navigate
                    class="btn-fill w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-trenakt-dark dark:bg-white/10 text-white text-sm font-semibold rounded-md px-7 py-3.5 transition"
                    style="--btn-fill-color: var(--color-trenakt-accent);">
                    <span class="relative">Promote my business</span>
                </a>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
        <div class="text-center max-w-2xl mx-auto mb-16" data-reveal>
            <p class="text-xs font-semibold uppercase tracking-wider text-trenakt-primary mb-2">How it works</p>
            <h2 class="text-3xl sm:text-4xl font-bold">Three steps to get started</h2>
        </div>

        <div class="grid sm:grid-cols-3 gap-8">
            <div class="text-center" data-reveal>
                <div class="w-14 h-14 rounded-full bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary flex items-center justify-center mx-auto mb-5">
                    <x-icon name="user" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold mb-2">Create your account</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Sign up in minutes and tell us if you want to earn, promote, or both.</p>
            </div>

            <div class="text-center" data-reveal>
                <div class="w-14 h-14 rounded-full bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary flex items-center justify-center mx-auto mb-5">
                    <x-icon name="briefcase" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold mb-2">Pick what fits you</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Browse tasks that pay, or set up a campaign for testers and feedback.</p>
            </div>

            <div class="text-center" data-reveal>
                <div class="w-14 h-14 rounded-full bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary flex items-center justify-center mx-auto mb-5">
                    <x-icon name="wallet" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-semibold mb-2">Get results</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Earners get paid for completed work. Businesses get real feedback from real people.</p>
            </div>
        </div>
    </section>

    {{-- For earners --}}
    <section id="for-earners" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-trenakt-primary mb-2">For earners</p>
                <h2 class="text-3xl sm:text-4xl font-bold mb-6">Turn your spare time into income</h2>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <x-icon name="check" class="w-5 h-5 text-trenakt-primary mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-300">Complete simple tasks like testing apps, giving feedback, and sharing opinions.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <x-icon name="check" class="w-5 h-5 text-trenakt-primary mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-300">Work whenever you want, from your phone, with no fixed hours.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <x-icon name="check" class="w-5 h-5 text-trenakt-primary mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-300">Get paid in your local currency, whether you're in Lagos, Accra, or anywhere in between.</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}" wire:navigate
                    class="btn-fill inline-flex items-center gap-2 bg-trenakt-primary text-white text-sm font-semibold rounded-md px-6 py-3 mt-8 transition">
                    <span class="relative">Start earning today</span>
                </a>
            </div>
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-100 dark:border-white/10 rounded-2xl p-8 shadow-sm" data-reveal>
                <div class="space-y-4">
                    <div class="flex items-center justify-between bg-trenakt-bg dark:bg-white/5 rounded-lg p-4">
                        <span class="text-sm font-medium">App testing task</span>
                        <span class="text-sm font-semibold text-trenakt-primary">₦1,500</span>
                    </div>
                    <div class="flex items-center justify-between bg-trenakt-bg dark:bg-white/5 rounded-lg p-4">
                        <span class="text-sm font-medium">Product feedback survey</span>
                        <span class="text-sm font-semibold text-trenakt-primary">₦800</span>
                    </div>
                    <div class="flex items-center justify-between bg-trenakt-bg dark:bg-white/5 rounded-lg p-4">
                        <span class="text-sm font-medium">Social media review</span>
                        <span class="text-sm font-semibold text-trenakt-primary">₦1,200</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- For businesses --}}
    <section id="for-businesses" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="order-2 lg:order-1 bg-trenakt-dark text-white rounded-2xl p-8 shadow-sm" data-reveal>
                <p class="text-sm text-white/70 mb-4">Campaign types</p>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 bg-white/10 rounded-lg p-4">
                        <x-icon name="briefcase" class="w-5 h-5 text-trenakt-accent-light" />
                        <span class="text-sm font-medium">Publicity campaigns</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 rounded-lg p-4">
                        <x-icon name="briefcase" class="w-5 h-5 text-trenakt-accent-light" />
                        <span class="text-sm font-medium">App and product testing</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/10 rounded-lg p-4">
                        <x-icon name="briefcase" class="w-5 h-5 text-trenakt-accent-light" />
                        <span class="text-sm font-medium">Market research</span>
                    </div>
                </div>
            </div>
            <div class="order-1 lg:order-2" data-reveal>
                <p class="text-xs font-semibold uppercase tracking-wider text-trenakt-accent mb-2">For businesses</p>
                <h2 class="text-3xl sm:text-4xl font-bold mb-6">Reach real people, not bots</h2>
                <ul class="space-y-4">
                    <li class="flex items-start gap-3">
                        <x-icon name="check" class="w-5 h-5 text-trenakt-accent mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-300">Run publicity campaigns and get your product in front of an active audience.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <x-icon name="check" class="w-5 h-5 text-trenakt-accent mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-300">Recruit testers for your app or product and collect honest feedback fast.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <x-icon name="check" class="w-5 h-5 text-trenakt-accent mt-0.5 shrink-0" />
                        <span class="text-gray-600 dark:text-gray-300">Get market research from real people in Nigeria and Ghana, not generic panels.</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}" wire:navigate
                    class="btn-fill inline-flex items-center gap-2 bg-trenakt-dark dark:bg-white text-white dark:text-trenakt-dark text-sm font-semibold rounded-md px-6 py-3 mt-8 transition"
                    style="--btn-fill-color: var(--color-trenakt-accent);">
                    <span class="relative">Start a campaign</span>
                </a>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
        <div class="text-center mb-12" data-reveal>
            <p class="text-xs font-semibold uppercase tracking-wider text-trenakt-primary mb-2">Questions</p>
            <h2 class="text-3xl sm:text-4xl font-bold">Frequently asked questions</h2>
        </div>

        <div class="space-y-3" data-reveal x-data="{ openIndex: null }">
            @php
                $faqs = [
                    ['q' => 'Can I both earn and promote on Trenakt?', 'a' => 'Yes. You pick one when you sign up, but you can activate the other side of Trenakt at any time from your dashboard. Nothing locks you in.'],
                    ['q' => 'How do I get paid for completed tasks?', 'a' => 'Payments are tracked in your wallet and paid out in your local currency, whether that is Naira or Cedi.'],
                    ['q' => 'What countries does Trenakt support?', 'a' => 'Trenakt currently supports Nigeria and Ghana, with more countries planned as we grow.'],
                    ['q' => 'Is there a fee to join as an earner?', 'a' => 'No. Creating an account and completing tasks is free for earners.'],
                    ['q' => 'What kind of campaigns can businesses run?', 'a' => 'Publicity campaigns, app and product testing, and market research, all aimed at reaching real people rather than automated traffic.'],
                ];
            @endphp

            @foreach ($faqs as $index => $faq)
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-100 dark:border-white/10 rounded-lg overflow-hidden">
                    <button type="button" @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                        class="w-full flex items-center justify-between gap-4 text-left px-5 py-4">
                        <h3 class="text-sm font-semibold">{{ $faq['q'] }}</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                            class="w-4 h-4 shrink-0 transition-transform" :class="openIndex === {{ $index }} ? 'rotate-180' : ''">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    <div x-show="openIndex === {{ $index }}" x-cloak x-transition class="px-5 pb-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
        <div class="bg-trenakt-primary rounded-2xl px-6 sm:px-12 py-16 text-center text-white" data-reveal>
            <h2 class="text-3xl sm:text-4xl font-bold mb-4">Ready to get started?</h2>
            <p class="text-white/85 max-w-xl mx-auto mb-8">
                Join Trenakt today and start earning, or put your business in front of people who matter.
            </p>
            <a href="{{ route('register') }}" wire:navigate
                class="btn-fill inline-flex items-center gap-2 bg-white text-trenakt-primary text-sm font-semibold rounded-md px-7 py-3.5 transition"
                style="--btn-fill-color: #E6F4EC;">
                <span class="relative">Create your free account</span>
            </a>
        </div>
    </section>
</x-layouts.public>