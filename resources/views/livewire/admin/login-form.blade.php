<div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6">
    <h1 class="text-xl font-bold mb-1">Sign in</h1>
    <p class="text-sm text-gray-500 dark:text-white/50 mb-6">Staff access to the Trenakt admin console.</p>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Email</label>
            <input wire:model="email" type="email" autofocus autocomplete="username"
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
            @error('email') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Password</label>
            <input wire:model="password" type="password" autocomplete="current-password"
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
            @error('password') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
            <x-checkbox wire:model="remember" />
            Keep me signed in
        </label>

        <button type="submit" wire:loading.attr="disabled" wire:target="login"
            class="w-full bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition disabled:opacity-60">
            <span wire:loading.remove wire:target="login">Sign in</span>
            <span wire:loading wire:target="login">Signing in...</span>
        </button>
    </form>
</div>
