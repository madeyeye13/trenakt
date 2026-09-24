<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Administration</p>
            <h1 class="text-2xl font-bold">Roles & permissions</h1>
            <p class="text-sm text-gray-500 dark:text-white/50 mt-1">Define what each kind of staff member can see and do in the admin console.</p>
        </div>
        <button type="button" wire:click="create" class="flex items-center gap-2 bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition shrink-0">
            <x-icon name="plus" class="w-4 h-4" />
            Add role
        </button>
    </div>

    @if ($roles->isEmpty())
        <x-empty-state icon="key" title="No roles yet"
            description="Create a role - like Task Verifier or Support - and pick which sections of the admin console it can access." />
    @else
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($roles as $role)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-trenakt-accent/10 text-trenakt-accent flex items-center justify-center shrink-0">
                                <x-icon name="key" class="w-4.5 h-4.5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">
                                    {{ str($role->name)->replace('_', ' ')->title() }}
                                    @if ($role->name === 'super_admin')
                                        <span class="text-[10px] uppercase tracking-wide bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary rounded-full px-2 py-0.5 ml-1 align-middle">Full access</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400 dark:text-white/40">
                                    @if ($role->name === 'super_admin')
                                        All permissions
                                    @else
                                        {{ $role->permissions_count }} {{ \Illuminate\Support\Str::plural('permission', $role->permissions_count) }}
                                    @endif
                                    &middot; {{ $role->users_count }} {{ \Illuminate\Support\Str::plural('staff member', $role->users_count) }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0">
                            <button type="button" wire:click="edit({{ $role->id }})" class="text-xs font-medium text-trenakt-accent hover:underline">
                                {{ $role->name === 'super_admin' ? 'View' : 'Edit' }}
                            </button>
                            @if ($role->name !== 'super_admin')
                                <button type="button" @click="$dispatch('open-modal', { name: 'delete-role', id: {{ $role->id }} })" class="text-xs font-medium text-trenakt-danger hover:underline">Delete</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-modal name="role-form" maxWidth="lg">
        <h3 class="text-lg font-semibold mb-4">{{ $editingId ? ($name === 'super_admin' ? 'Super admin' : 'Edit role') : 'Add role' }}</h3>

        <div class="mb-5">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Role name</label>
            <input wire:model="name" type="text" placeholder="e.g. Task Verifier" @disabled($name === 'super_admin')
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent disabled:opacity-60">
            @error('name') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="mb-2">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Permissions</label>

            @if ($name === 'super_admin')
                <p class="text-sm text-gray-500 dark:text-gray-400">The super admin role always has every permission, including ones added later - it can't be trimmed down.</p>
            @else
                <div class="space-y-4 max-h-80 overflow-y-auto scrollbar-brand pr-1">
                    @foreach ($groupedPermissions as $group => $permissions)
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1.5">{{ $group }}</p>
                            <div class="grid sm:grid-cols-2 gap-2">
                                @foreach ($permissions as $permName => $meta)
                                    <label class="flex items-center gap-2 text-sm px-3 py-2 rounded-md border border-gray-200 dark:border-white/10 cursor-pointer">
                                        <x-checkbox wire:model="selectedPermissions" value="{{ $permName }}" />
                                        {{ $meta['label'] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">
                {{ $name === 'super_admin' ? 'Close' : 'Cancel' }}
            </button>
            @if ($name !== 'super_admin')
                <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                    class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Save</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            @endif
        </div>
    </x-modal>

    <x-modal name="delete-role" maxWidth="sm">
        <div x-data="{ deleteId: null }" x-on:open-modal.window="if ($event.detail.name === 'delete-role') deleteId = $event.detail.id">
            <h3 class="text-lg font-semibold mb-2">Delete this role?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Staff currently on this role won't lose their account, but they'll lose whatever access it granted until you give them a new role.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
                <button type="button" @click="$wire.delete(deleteId)" class="bg-trenakt-danger text-white text-sm font-medium rounded-md px-4 py-2">Delete</button>
            </div>
        </div>
    </x-modal>
</div>
