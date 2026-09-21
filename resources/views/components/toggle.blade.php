@props(['model', 'checked' => false])

<div x-data="{ on: @js($checked) }">
    <button type="button" @click="on = !on; $wire.set('{{ $model }}', on)"
        class="relative inline-flex h-6 w-11 items-center rounded-full transition"
        :class="on ? 'bg-trenakt-primary' : 'bg-gray-200 dark:bg-white/10'">
        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition"
            :class="on ? 'translate-x-6' : 'translate-x-1'"></span>
    </button>
</div>