@props(['onDark' => false])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <img src="{{ asset('images/Trenaktlogo.png') }}" alt="Trenakt" class="w-9 h-9 object-contain shrink-0">
    <span class="text-xl font-bold tracking-tight {{ $onDark ? 'text-white' : 'text-trenakt-dark dark:text-white' }}">
        Tren<span class="{{ $onDark ? 'text-trenakt-accent-light' : 'text-trenakt-accent dark:text-trenakt-accent-light' }}">a</span>kt
    </span>
</div>