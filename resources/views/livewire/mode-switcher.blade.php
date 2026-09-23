@php
    $nextMode = auth()->user()->activeMode() === 'business' ? 'Earning' : 'Promoting';
@endphp

<div>
    <button type="button" wire:click="switch" wire:loading.attr="disabled"
        class="inline-flex items-center gap-1.5 whitespace-nowrap text-sm font-medium text-trenakt-primary hover:text-trenakt-primary/80 dark:text-trenakt-accent-light dark:hover:text-trenakt-accent-light/80 transition disabled:opacity-60">
        <x-icon name="switch" class="w-4 h-4 shrink-0" wire:loading.class="animate-spin" />
        <span wire:loading.remove><span class="hidden sm:inline">Switch to </span>{{ $nextMode }}</span>
        <span wire:loading>Switching...</span>
    </button>
</div>