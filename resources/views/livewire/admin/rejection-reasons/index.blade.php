<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Earning</p>
            <h1 class="text-2xl font-bold">Rejection reasons</h1>
            <p class="text-sm text-gray-500 dark:text-white/50 mt-1">The preset buttons admins pick from when rejecting a submission.</p>
        </div>
        <button type="button" wire:click="create" class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition">
            Add reason
        </button>
    </div>

    @if ($reasons->isEmpty())
        <x-empty-state icon="x-circle" title="No rejection reasons yet"
            description="Add a few preset reasons so admins can reject quickly and consistently." />
    @else
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($reasons as $reason)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0 flex items-center gap-3">
                            <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $reason->is_active ? 'bg-trenakt-success-light text-trenakt-success' : 'bg-gray-100 dark:bg-white/5 text-gray-400' }}">
                                {{ $reason->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <p class="text-sm font-medium truncate">{{ $reason->label }}</p>
                        </div>
                        <div class="flex items-center gap-4 shrink-0">
                            <button type="button" wire:click="toggleActive({{ $reason->id }})" class="text-xs font-medium text-gray-500 dark:text-gray-400 hover:underline">
                                {{ $reason->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                            <button type="button" wire:click="edit({{ $reason->id }})" class="text-xs font-medium text-trenakt-accent hover:underline">Edit</button>
                            <button type="button" @click="$dispatch('open-modal', { name: 'delete-reason', id: {{ $reason->id }} })" class="text-xs font-medium text-trenakt-danger hover:underline">Delete</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-modal name="reason-form" maxWidth="sm">
        <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit reason' : 'Add reason' }}</h3>

        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Label</label>
        <input wire:model="label" type="text" placeholder="e.g. Screenshot doesn't match the task"
            class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
        @error('label') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror

        <div class="flex items-center justify-between mt-4">
            <span class="text-sm text-gray-600 dark:text-gray-300">Active</span>
            <x-toggle model="isActive" :checked="$isActive" />
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </x-modal>

    <x-modal name="delete-reason">
        <div x-data="{ deleteId: null }" x-on:open-modal.window="if ($event.detail.name === 'delete-reason') deleteId = $event.detail.id">
            <h3 class="text-lg font-semibold mb-2">Delete this reason?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Past rejections that used it keep their recorded text. This only removes it from future use.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
                <button type="button" @click="$wire.delete(deleteId)" class="bg-trenakt-danger text-white text-sm font-medium rounded-md px-4 py-2">Delete</button>
            </div>
        </div>
    </x-modal>
</div>
