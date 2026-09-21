@props(['icon' => 'briefcase', 'title' => 'Nothing here yet', 'description' => null])

<div class="flex flex-col items-center justify-center text-center py-16 px-6">
    <div class="w-14 h-14 rounded-full bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary flex items-center justify-center mb-4">
        <x-icon name="{{ $icon }}" class="w-6 h-6" />
    </div>
    <h3 class="text-base font-semibold text-trenakt-dark dark:text-white mb-1">{{ $title }}</h3>
    @if ($description)
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-6">{{ $description }}</p>
    @endif
    {{ $slot ?? '' }}
</div>