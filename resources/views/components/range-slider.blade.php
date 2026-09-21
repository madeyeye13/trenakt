@props(['model', 'label' => '', 'min' => 0, 'max' => 100, 'step' => 1, 'value' => 0, 'suffix' => ''])

<div x-data="{ value: {{ $value }}, editing: false }">
    <div class="flex items-center justify-between mb-1.5">
        <span class="text-sm font-medium text-trenakt-dark dark:text-white">{{ $label }}</span>

        <span x-show="!editing" @click="editing = true; $nextTick(() => $refs.input.focus())"
            class="text-sm font-semibold text-trenakt-primary cursor-pointer hover:underline"
            x-text="value + '{{ $suffix }}'"></span>

        <input x-show="editing" x-ref="input" type="number"
            min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
            x-model.number="value"
            @blur="editing = false; if (value < {{ $min }}) value = {{ $min }}; if (value > {{ $max }}) value = {{ $max }}; $wire.set('{{ $model }}', value)"
            @keydown.enter="$event.target.blur()"
            class="w-24 text-right text-sm font-semibold text-trenakt-primary border border-trenakt-primary/30 rounded px-2 py-0.5 focus:outline-none">
    </div>
    <input type="range" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
        x-model.number="value" @input="$wire.set('{{ $model }}', value)"
        class="w-full h-2 rounded-full appearance-none cursor-pointer bg-gray-200 dark:bg-white/10 accent-trenakt-primary">
    <div class="flex items-center justify-between mt-1">
        <span class="text-xs text-gray-400 dark:text-white/30">{{ $min }}{{ $suffix }}</span>
        <span class="text-xs text-gray-400 dark:text-white/30">{{ $max }}{{ $suffix }}</span>
    </div>
</div>