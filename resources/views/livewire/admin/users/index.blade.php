<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Administration</p>
            <h1 class="text-2xl font-bold">Registered users</h1>
            <p class="text-sm text-gray-500 dark:text-white/50 mt-1">Everyone who has signed up on Trenakt as a participant or business.</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-5">
        <div class="relative max-w-sm flex-1 min-w-[16rem]">
            <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Search by name or email..."
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
        </div>

        {{-- `mode` (Alpine, local) flips the pill highlight the instant you
             click. wire:click fires the real Livewire request that
             re-filters the list right behind it - the two are independent,
             so the highlight never waits on the round trip. (An earlier
             version tried @entangle('mode') for this, but Livewire 3/4
             dropped that directive in favour of $wire.entangle(), so it
             silently failed to bind at all - clicks changed nothing.) --}}
        <div x-data="{ mode: @js($mode) }" class="flex items-center gap-1 bg-gray-100 dark:bg-white/5 rounded-md p-1 text-sm">
            <button type="button" @click="mode = 'all'" wire:click="$set('mode', 'all')" class="px-3 py-1.5 rounded transition-colors" :class="mode === 'all' ? 'bg-white dark:bg-trenakt-surface-dark shadow-sm font-medium' : 'text-gray-500 dark:text-white/50'">All</button>
            <button type="button" @click="mode = 'participant'" wire:click="$set('mode', 'participant')" class="px-3 py-1.5 rounded transition-colors" :class="mode === 'participant' ? 'bg-white dark:bg-trenakt-surface-dark shadow-sm font-medium' : 'text-gray-500 dark:text-white/50'">Participants</button>
            <button type="button" @click="mode = 'business'" wire:click="$set('mode', 'business')" class="px-3 py-1.5 rounded transition-colors" :class="mode === 'business' ? 'bg-white dark:bg-trenakt-surface-dark shadow-sm font-medium' : 'text-gray-500 dark:text-white/50'">Businesses</button>
        </div>
    </div>

    @if ($users->isEmpty())
        <x-empty-state icon="users" title="No users found" description="Try a different search or filter." />
    @else
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($users as $user)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0 flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-trenakt-primary text-white flex items-center justify-center text-sm font-semibold shrink-0">
                                {{ strtoupper(substr($user->name ?: '?', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $user->name ?: 'Unnamed' }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 shrink-0">
                            <div class="hidden sm:flex flex-wrap gap-1.5 justify-end">
                                @foreach ($user->roles as $role)
                                    <span class="text-[10px] uppercase tracking-wide bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary rounded-full px-2 py-0.5">
                                        {{ str($role->name)->title() }}
                                    </span>
                                @endforeach
                            </div>
                            <span class="hidden md:inline text-xs text-gray-400 dark:text-white/40">Joined {{ $user->created_at->format('M j, Y') }}</span>
                            <button type="button" @click="$dispatch('open-modal', { name: 'delete-user', id: {{ $user->id }}, name_: {{ Js::from($user->name ?: $user->email) }} })"
                                class="text-xs font-medium text-trenakt-danger hover:underline">Delete</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-5">{{ $users->links() }}</div>
    @endif

    <x-modal name="delete-user" maxWidth="sm">
        <div x-data="{ deleteId: null, deleteName: '' }"
            x-on:open-modal.window="if ($event.detail.name === 'delete-user') { deleteId = $event.detail.id; deleteName = $event.detail.name_; }">
            <h3 class="text-lg font-semibold mb-2">Delete <span x-text="deleteName"></span>?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">This permanently removes their account, wallet, and submission history from Trenakt. This can't be undone.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
                <button type="button" @click="$wire.delete(deleteId)" class="bg-trenakt-danger text-white text-sm font-medium rounded-md px-4 py-2">Delete</button>
            </div>
        </div>
    </x-modal>
</div>
