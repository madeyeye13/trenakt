@props(['value' => null])

{{--
    A real native <input type="checkbox"> underneath (kept fully accessible
    and wire:model-able - it just gets Livewire/HTML attributes forwarded
    onto it via $attributes as usual), with the actual checkbox glyph
    replaced by two sibling layers driven by Tailwind's peer-checked
    variant: a bordered box and a checkmark, both absolutely positioned
    over the invisible input so the whole area stays clickable. No JS.
--}}
<span class="relative inline-flex items-center justify-center w-4.5 h-4.5 shrink-0">
    <input type="checkbox"
        @if (! is_null($value)) value="{{ $value }}" @endif
        {{ $attributes->merge(['class' => 'peer absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10']) }}>
    <span class="absolute inset-0 rounded border border-gray-300 dark:border-white/20 bg-white dark:bg-white/5 peer-checked:bg-trenakt-accent peer-checked:border-trenakt-accent peer-focus-visible:ring-2 peer-focus-visible:ring-trenakt-accent/40 transition-colors"></span>
    <svg class="relative w-3 h-3 text-white opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
    </svg>
</span>
