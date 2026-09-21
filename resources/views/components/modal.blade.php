@props(['name', 'maxWidth' => 'md'])

<div
    x-data="{ show: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') show = true"
    x-on:close-modal.window="if (!$event.detail || $event.detail === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black/40" @click="show = false"></div>

    <div class="flex min-h-screen items-center justify-center p-4">
        <div @click.stop x-show="show" x-transition
            class="relative bg-white dark:bg-trenakt-surface-dark rounded-lg shadow-xl w-full max-w-{{ $maxWidth }} p-6">
            {{ $slot }}
        </div>
    </div>
</div>