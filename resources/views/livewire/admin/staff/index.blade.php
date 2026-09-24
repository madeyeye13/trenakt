<div>
    <div class="flex items-center justify-between mb-6 gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Administration</p>
            <h1 class="text-2xl font-bold">Staff</h1>
            <p class="text-sm text-gray-500 dark:text-white/50 mt-1">Everyone with access to this admin console, and what role they hold.</p>
        </div>
        <button type="button" wire:click="create" class="flex items-center gap-2 bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition shrink-0">
            <x-icon name="user-plus" class="w-4 h-4" />
            Add staff
        </button>
    </div>

    <div class="relative mb-5 max-w-sm">
        <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Search staff..."
            class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
    </div>

    @if ($staff->isEmpty())
        <x-empty-state icon="users" title="No staff yet"
            description="Add your first staff member and assign them a role so they can sign in to this console." />
    @else
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($staff as $member)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-trenakt-accent text-white flex items-center justify-center text-sm font-semibold shrink-0">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $member->name }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ $member->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0">
                            <div class="hidden sm:flex flex-wrap gap-1.5 justify-end max-w-[14rem]">
                                @forelse ($member->roles as $role)
                                    <span class="text-[10px] uppercase tracking-wide bg-trenakt-accent/10 text-trenakt-accent rounded-full px-2 py-0.5">
                                        {{ str($role->name)->replace('_', ' ')->title() }}
                                    </span>
                                @empty
                                    <span class="text-[10px] uppercase tracking-wide bg-gray-100 dark:bg-white/5 text-gray-400 rounded-full px-2 py-0.5">No role</span>
                                @endforelse
                            </div>
                            <button type="button" wire:click="edit({{ $member->id }})" class="text-xs font-medium text-trenakt-accent hover:underline">Edit</button>
                            @if ($member->id !== auth()->id())
                                <button type="button" @click="$dispatch('open-modal', { name: 'delete-staff', id: {{ $member->id }} })" class="text-xs font-medium text-trenakt-danger hover:underline">Remove</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-5">{{ $staff->links() }}</div>
    @endif

    <x-modal name="staff-form" maxWidth="lg">
        <h3 class="text-lg font-semibold mb-4">{{ $editingId ? 'Edit staff member' : 'Add staff' }}</h3>

        <div class="grid sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Full name</label>
                <input wire:model="name" type="text" placeholder="Jane Doe"
                    class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
                @error('name') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Email</label>
                <input wire:model="email" type="email" placeholder="jane@trenakt.com" @disabled($editingId && ! $isSuperAdmin)
                    class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent disabled:opacity-60">
                @error('email') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
                @if ($editingId && ! $isSuperAdmin)
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1.5">Only a super admin can change a staff email.</p>
                @endif
            </div>
        </div>

        <div class="mb-2">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Roles</label>
            <div class="flex flex-wrap gap-2">
                @forelse ($assignableRoles as $role)
                    <label class="flex items-center gap-2 text-sm px-3 py-2 rounded-md border border-gray-200 dark:border-white/10 cursor-pointer">
                        <x-checkbox wire:model="selectedRoles" value="{{ $role->name }}" />
                        {{ str($role->name)->replace('_', ' ')->title() }}
                    </label>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">No roles exist yet - create one on the Roles page first.</p>
                @endforelse
            </div>
            @error('selectedRoles') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
        </div>

        @if (! $editingId)
            <p class="text-xs text-gray-400 dark:text-white/40 mt-4">A temporary password will be generated and emailed to them.</p>
        @endif

        <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </x-modal>

    <x-modal name="delete-staff" maxWidth="sm">
        <div x-data="{ deleteId: null }" x-on:open-modal.window="if ($event.detail.name === 'delete-staff') deleteId = $event.detail.id">
            <h3 class="text-lg font-semibold mb-2">Remove this staff member?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">They'll immediately lose access to the admin console. This can't be undone.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
                <button type="button" @click="$wire.delete(deleteId)" class="bg-trenakt-danger text-white text-sm font-medium rounded-md px-4 py-2">Remove</button>
            </div>
        </div>
    </x-modal>
</div>
