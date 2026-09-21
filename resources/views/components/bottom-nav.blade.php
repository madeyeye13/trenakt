@php
    $mode = auth()->user()->activeMode();

    $earningItems = [
        ['label' => 'Home', 'route' => 'dashboard', 'icon' => 'grid', 'live' => true],
        ['label' => 'Tasks', 'route' => null, 'icon' => 'briefcase', 'live' => false],
        ['label' => 'Wallet', 'route' => null, 'icon' => 'wallet', 'live' => false],
        ['label' => 'Profile', 'route' => null, 'icon' => 'user', 'live' => false],
    ];

    $promotingItems = [
        ['label' => 'Home', 'route' => 'dashboard', 'icon' => 'grid', 'live' => true],
        ['label' => 'Campaigns', 'route' => 'campaigns.index', 'icon' => 'briefcase', 'live' => true],
        ['label' => 'Wallet', 'route' => null, 'icon' => 'wallet', 'live' => false],
        ['label' => 'Profile', 'route' => null, 'icon' => 'user', 'live' => false],
    ];

    $items = $mode === 'business' ? $promotingItems : $earningItems;
@endphp

<nav {{ $attributes->merge(['class' => 'fixed bottom-0 inset-x-0 bg-white dark:bg-trenakt-surface-dark border-t border-gray-200 dark:border-white/10 z-40']) }}
    style="padding-bottom: env(safe-area-inset-bottom, 0px);">
    <div class="grid grid-cols-4">
        @foreach ($items as $item)
            @if ($item['live'])
                <a href="{{ route($item['route']) }}" wire:navigate
                    class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium
                        {{ request()->routeIs($item['route']) ? 'text-trenakt-primary' : 'text-gray-400 dark:text-gray-500' }}">
                    <x-icon name="{{ $item['icon'] }}" class="w-5 h-5" />
                    {{ $item['label'] }}
                </a>
            @else
                <span class="flex flex-col items-center justify-center gap-1 py-2.5 text-[11px] font-medium text-gray-300 dark:text-gray-700">
                    <x-icon name="{{ $item['icon'] }}" class="w-5 h-5" />
                    {{ $item['label'] }}
                </span>
            @endif
        @endforeach
    </div>
</nav>