@props(['name', 'maxWidth' => 'md'])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div
    x-data="{ show: false }"
    x-on:open-modal.window="if ($event.detail.name === '{{ $name }}') show = true"
    x-on:close-modal.window="if (!$event.detail?.name || $event.detail.name === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto scrollbar-brand">
    <div class="fixed inset-0 bg-black/40" @click="show = false"></div>

    <div class="flex min-h-screen items-center justify-center p-4">
        <div @click.stop x-show="show" x-transition
            class="relative bg-white dark:bg-trenakt-surface-dark rounded-lg shadow-xl w-full {{ $maxWidthClass }} p-6">
            {{ $slot }}
        </div>
    </div>
</div>