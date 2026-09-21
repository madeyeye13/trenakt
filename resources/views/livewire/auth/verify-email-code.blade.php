<div>
    <h2 class="text-lg font-semibold mb-2">Check your email</h2>
    <p class="text-sm text-gray-500 mb-6">Enter the 6-digit code we sent to {{ auth()->user()->email }}.</p>

    @if ($status) <p class="text-sm text-trenakt-primary mb-4">{{ $status }}</p> @endif
    @if ($error) <p class="text-sm text-red-600 mb-4">{{ $error }}</p> @endif

    <input wire:model="code" type="text" maxlength="6" inputmode="numeric"
        class="w-full text-center text-2xl tracking-[0.5em] border border-gray-300 rounded-md py-3 focus:outline-none focus:border-trenakt-primary">

    <button type="button" wire:click="verify" wire:loading.attr="disabled" wire:target="verify"
        class="w-full mt-4 bg-trenakt-primary text-white rounded-md py-2.5 font-medium hover:opacity-90 transition disabled:opacity-60">
        <span wire:loading.remove wire:target="verify">Verify</span>
        <span wire:loading wire:target="verify">Verifying...</span>
    </button>

    <button type="button" wire:click="resend" wire:loading.attr="disabled" wire:target="resend"
        class="w-full mt-2 text-sm text-gray-500 hover:text-trenakt-dark disabled:opacity-60">
        <span wire:loading.remove wire:target="resend">Resend code</span>
        <span wire:loading wire:target="resend">Sending...</span>
    </button>
</div>