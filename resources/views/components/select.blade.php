@props(['model', 'options', 'selected' => null, 'placeholder' => 'Select...'])

<div x-data="{ open: false, value: @js($selected) }" class="relative">
    <button type="button" @click="open = !open" @click.outside="open = false"
        class="w-full flex items-center justify-between border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm text-left">
        <span x-text="value ? {{ Js::from($options) }}[value] : '{{ $placeholder }}'" :class="!value && 'text-gray-400'"></span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-400 transition" :class="open && 'rotate-180'">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div x-show="open" x-cloak x-transition @click.outside="open = false"
        class="absolute z-20 mt-1 w-full bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-md shadow-lg py-1 max-h-56 overflow-y-auto scrollbar-brand">
        @foreach ($options as $key => $label)
            <button type="button" @click="value = '{{ $key }}'; $wire.set('{{ $model }}', '{{ $key }}'); open = false"
                class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-white/5"
                :class="value === '{{ $key }}' && 'text-trenakt-primary font-medium'">
                {{ $label }}
            </button>
        @endforeach
    </div>
</div>