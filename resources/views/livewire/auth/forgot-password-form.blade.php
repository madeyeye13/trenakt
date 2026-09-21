<div>
    <a href="{{ route('login') }}" wire:navigate
        class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-trenakt-dark transition mb-6">
        <x-icon name="arrow-left" class="w-4 h-4" />
        Back to login
    </a>

    <h2 class="text-lg font-semibold mb-6">Reset your password</h2>
    @if ($status) <p class="text-sm text-trenakt-primary mb-4">{{ $status }}</p> @endif

    <input wire:model="email" type="email" placeholder="Email address"
        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-trenakt-primary">
    @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

    <button wire:click="send" wire:loading.attr="disabled" type="button"
        class="w-full mt-4 bg-trenakt-primary text-white rounded-md py-2 font-medium hover:opacity-90 transition disabled:opacity-60">
        <span wire:loading.remove>Send reset link</span>
        <span wire:loading>Sending...</span>
    </button>
</div>