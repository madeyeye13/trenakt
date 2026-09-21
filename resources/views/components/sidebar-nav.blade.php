@php
    $mode = auth()->user()->activeMode();

    $earningItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid', 'live' => true],
        ['label' => 'Available Tasks', 'route' => null, 'icon' => 'briefcase', 'live' => false],
        ['label' => 'Wallet', 'route' => null, 'icon' => 'wallet', 'live' => false],
        ['label' => 'Profile', 'route' => null, 'icon' => 'user', 'live' => false],
    ];

    $promotingItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid', 'live' => true],
        ['label' => 'Campaigns', 'route' => 'campaigns.index', 'icon' => 'briefcase', 'live' => true],
        ['label' => 'Wallet', 'route' => 'wallet.index', 'icon' => 'wallet', 'live' => true],
        ['label' => 'Profile', 'route' => null, 'icon' => 'user', 'live' => false],
    ];

    $items = $mode === 'business' ? $promotingItems : $earningItems;
@endphp

<aside {{ $attributes->merge(['class' => 'flex flex-col bg-white dark:bg-trenakt-surface-dark border-r border-gray-200 dark:border-white/10 h-screen sticky top-0']) }}>
    <div class="px-5 py-6">
        <x-trenakt-logo />
    </div>

    <div class="px-3 flex-1 overflow-y-auto">
        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Menu</p>

        <nav class="space-y-1">
            @foreach ($items as $item)
                @if ($item['live'])
                    <a href="{{ route($item['route']) }}" wire:navigate
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ request()->routeIs($item['route']) ? 'bg-trenakt-primary/10 text-trenakt-primary dark:bg-trenakt-primary/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                        <x-icon name="{{ $item['icon'] }}" class="w-4.5 h-4.5" />
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-300 dark:text-gray-600 cursor-not-allowed">
                        <span class="flex items-center gap-3">
                            <x-icon name="{{ $item['icon'] }}" class="w-4.5 h-4.5" />
                            {{ $item['label'] }}
                        </span>
                        <span class="text-[10px] uppercase tracking-wide bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-gray-500 rounded-full px-2 py-0.5">Soon</span>
                    </span>
                @endif
            @endforeach
        </nav>
    </div>

    <div class="border-t border-gray-200 dark:border-white/10 p-3">
        <div class="flex items-center gap-3 px-2 py-2">
            <div class="w-9 h-9 rounded-full bg-trenakt-primary text-white flex items-center justify-center text-sm font-semibold shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-trenakt-dark dark:text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $mode === 'business' ? 'Promoting' : 'Earning' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-trenakt-dark dark:hover:text-white transition" title="Log out">
                    <x-icon name="logout" class="w-4.5 h-4.5" />
                </button>
            </form>
        </div>
    </div>
</aside>