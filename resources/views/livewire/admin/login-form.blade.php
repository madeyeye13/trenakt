<div>
    @if ($error)
        <div class="bg-trenakt-danger/10 border border-trenakt-danger/30 text-trenakt-danger text-sm rounded-md px-4 py-3 mb-5">
            {{ $error }}
        </div>
    @endif

    <div class="space-y-4">
        <div>
            <label class="text-xs font-medium text-white/50 uppercase tracking-wide">Email</label>
            <input wire:model="email" type="email"
                class="w-full mt-1.5 bg-white/5 border border-white/10 rounded-md px-3 py-2.5 text-white placeholder-white/30 focus:outline-none focus:border-trenakt-accent">
            @error('email') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs font-medium text-white/50 uppercase tracking-wide">Password</label>
            <input wire:model="password" type="password"
                class="w-full mt-1.5 bg-white/5 border border-white/10 rounded-md px-3 py-2.5 text-white placeholder-white/30 focus:outline-none focus:border-trenakt-accent">
            @error('password') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
        </div>

        <button wire:click="login" wire:loading.attr="disabled" type="button"
            class="w-full bg-trenakt-primary text-white text-sm font-semibold rounded-md py-2.5 hover:opacity-90 transition disabled:opacity-60">
            <span wire:loading.remove>Sign in</span>
            <span wire:loading>Verifying...</span>
        </button>
    </div>
</div>