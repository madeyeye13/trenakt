<div>
    <a href="{{ route('login') }}" wire:navigate
        class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-trenakt-dark transition mb-6">
        <x-icon name="arrow-left" class="w-4 h-4" />
        Back to login
    </a>

    <h2 class="text-lg font-semibold mb-6">Set a new password</h2>
    @if ($status) <p class="text-sm text-red-600 mb-4">{{ $status }}</p> @endif

    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium">Email</label>
            <input wire:model="email" type="email" class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-trenakt-primary">
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium">New password</label>
            <input wire:model="password" type="password" class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-trenakt-primary">
            @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-medium">Confirm new password</label>
            <input wire:model="password_confirmation" type="password" class="w-full mt-1 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-trenakt-primary">
        </div>
        <button wire:click="resetPassword" wire:loading.attr="disabled" type="button"
            class="w-full bg-trenakt-primary text-white rounded-md py-2 font-medium hover:opacity-90 transition disabled:opacity-60">
            <span wire:loading.remove>Reset password</span>
            <span wire:loading>Resetting...</span>
        </button>
    </div>
</div>