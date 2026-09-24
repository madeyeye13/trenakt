<div class="max-w-2xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Account</p>
        <h1 class="text-2xl font-bold">My account</h1>
        <p class="text-sm text-gray-500 dark:text-white/50 mt-1">
            {{ $isSuperAdmin ? 'As super admin, you can change your own email and password.' : 'You can change your password here. Only a super admin can change your email - ask them if it needs updating.' }}
        </p>
    </div>

    <form wire:submit="saveProfile" class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 space-y-5 mb-6">
        <h2 class="text-sm font-semibold">Profile</h2>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Full name</label>
            <input wire:model="name" type="text"
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
            @error('name') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Email</label>
            <input wire:model="email" type="email" @disabled(! $isSuperAdmin)
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent disabled:opacity-60">
            @error('email') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile"
                class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="saveProfile">Save profile</span>
                <span wire:loading wire:target="saveProfile">Saving...</span>
            </button>
        </div>
    </form>

    <form wire:submit="changePassword" class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 space-y-5">
        <h2 class="text-sm font-semibold">Change password</h2>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Current password</label>
            <input wire:model="currentPassword" type="password"
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
            @error('currentPassword') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">New password</label>
                <input wire:model="newPassword" type="password"
                    class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
                @error('newPassword') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Confirm new password</label>
                <input wire:model="newPassword_confirmation" type="password"
                    class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="changePassword"
                class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="changePassword">Change password</span>
                <span wire:loading wire:target="changePassword">Saving...</span>
            </button>
        </div>
    </form>
</div>
