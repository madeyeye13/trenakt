<div x-data="{ toasts: [] }"
    x-on:toast.window="
        const id = Date.now();
        toasts.push({ id, type: $event.detail.type ?? 'success', message: $event.detail.message });
        setTimeout(() => { toasts = toasts.filter(t => t.id !== id) }, 4000);
    "
    class="fixed top-4 inset-x-4 sm:inset-x-auto sm:right-4 z-50 space-y-3 sm:w-96">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="bg-white dark:bg-trenakt-surface-dark rounded-xl shadow-lg border border-gray-100 dark:border-white/10 p-4 flex items-start gap-3">

            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                :class="{
                    'bg-trenakt-success-light text-trenakt-success': toast.type === 'success',
                    'bg-trenakt-danger-light text-trenakt-danger': toast.type === 'error',
                    'bg-trenakt-warning-light text-trenakt-warning': toast.type === 'warning',
                    'bg-trenakt-info-light text-trenakt-info': toast.type === 'info',
                }">
                <x-icon name="check-circle" class="w-4.5 h-4.5" x-show="toast.type === 'success'" />
                <x-icon name="x-circle" class="w-4.5 h-4.5" x-show="toast.type === 'error'" x-cloak />
                <x-icon name="alert-triangle" class="w-4.5 h-4.5" x-show="toast.type === 'warning'" x-cloak />
                <x-icon name="info-circle" class="w-4.5 h-4.5" x-show="toast.type === 'info'" x-cloak />
            </div>

            <p class="text-sm text-trenakt-dark dark:text-white flex-1 pt-1" x-text="toast.message"></p>

            <button type="button" @click="toasts = toasts.filter(t => t.id !== toast.id)" class="text-gray-300 hover:text-trenakt-dark dark:hover:text-white shrink-0 mt-1">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>
    </template>
</div>