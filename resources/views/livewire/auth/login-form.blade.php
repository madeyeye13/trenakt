<div>
    <h2 class="text-lg font-semibold mb-6">Log in to Trenakt</h2>
    @if ($error) <p class="text-sm text-red-600 mb-4">{{ $error }}</p> @endif

    <div class="space-y-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Email</label>
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <x-icon name="mail" class="w-4 h-4" />
                </span>
                <input wire:model="email" type="email"
                    class="w-full border border-gray-300 rounded-md pl-9 pr-3 py-2.5 focus:outline-none focus:border-trenakt-primary">
            </div>
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ show: false }">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700">Password</label>
                <a href="/forgot-password" wire:navigate class="text-xs text-trenakt-primary hover:underline">Forgot password?</a>
            </div>
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <x-icon name="lock" class="w-4 h-4" />
                </span>
                <input wire:model="password" :type="show ? 'text' : 'password'"
                    class="w-full border border-gray-300 rounded-md pl-9 pr-10 py-2.5 focus:outline-none focus:border-trenakt-primary">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                    <x-icon name="eye" class="w-4 h-4" x-show="!show" x-cloak />
                    <x-icon name="eye-slash" class="w-4 h-4" x-show="show" x-cloak />
                </button>
            </div>
        </div>

        <label class="flex items-center gap-2 cursor-pointer select-none" x-data="{ checked: @js($remember) }">
            <span class="relative inline-flex items-center justify-center w-4.5 h-4.5 rounded border transition"
                @click="checked = !checked; $wire.remember = checked"
                :class="checked ? 'bg-trenakt-primary border-trenakt-primary' : 'bg-white border-gray-300'">
                <x-icon name="check" class="w-3 h-3 text-white" x-show="checked" x-cloak />
            </span>
            <span class="text-sm text-gray-600">Remember me</span>
        </label>

        <button type="button" wire:click="login" wire:loading.attr="disabled" wire:target="login"
            class="w-full bg-trenakt-primary text-white rounded-md py-2.5 font-medium hover:opacity-90 transition disabled:opacity-60">
            <span wire:loading.remove wire:target="login">Log in</span>
            <span wire:loading wire:target="login">Logging in...</span>
        </button>
    </div>

    <p class="text-sm text-gray-500 text-center mt-6">
        Don't have an account?
        <a href="/register" wire:navigate class="text-trenakt-primary font-medium hover:underline">Create one</a>
    </p>
</div>