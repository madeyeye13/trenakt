<div x-data="{ step: 1, intent: @js($intent), country_id: @js($country_id) }" x-on:step-2-validated.window="step = 3">
    <div x-show="step === 1" x-cloak>
        <h2 class="text-lg font-semibold mb-1">What would you like to do?</h2>
        <p class="text-sm text-gray-500 mb-6">You can do both anytime.</p>
        <div class="space-y-3">
            <button type="button" @click="intent = 'participant'; $wire.intent = 'participant'; step = 2"
                class="w-full text-left border rounded-lg p-4 transition"
                :class="intent === 'participant' ? 'border-trenakt-primary bg-trenakt-bg' : 'border-gray-200 hover:border-gray-300'">
                <p class="font-medium">Earn</p>
                <p class="text-sm text-gray-500">Complete tasks, test products, share feedback and get paid.</p>
            </button>
            <button type="button" @click="intent = 'business'; $wire.intent = 'business'; step = 2"
                class="w-full text-left border rounded-lg p-4 transition"
                :class="intent === 'business' ? 'border-trenakt-primary bg-trenakt-bg' : 'border-gray-200 hover:border-gray-300'">
                <p class="font-medium">Promote</p>
                <p class="text-sm text-gray-500">Get your business, product, app, event or idea in front of the right people.</p>
            </button>
        </div>
    </div>

    <div x-show="step === 2" x-cloak>
        <h2 class="text-lg font-semibold mb-6">Create your account</h2>
        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-700">Full name</label>
                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <x-icon name="user" class="w-4 h-4" />
                    </span>
                    <input wire:model="name" type="text"
                        class="w-full border border-gray-300 rounded-md pl-9 pr-3 py-2.5 focus:outline-none focus:border-trenakt-primary">
                </div>
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

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
                <label class="text-sm font-medium text-gray-700">Password</label>
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
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div x-data="{ show: false }">
                <label class="text-sm font-medium text-gray-700">Confirm password</label>
                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <x-icon name="lock" class="w-4 h-4" />
                    </span>
                    <input wire:model="password_confirmation" :type="show ? 'text' : 'password'"
                        class="w-full border border-gray-300 rounded-md pl-9 pr-10 py-2.5 focus:outline-none focus:border-trenakt-primary">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                        <x-icon name="eye" class="w-4 h-4" x-show="!show" x-cloak />
                        <x-icon name="eye-slash" class="w-4 h-4" x-show="show" x-cloak />
                    </button>
                </div>
            </div>

            @error('intent') <p class="text-xs text-red-600 mb-2">{{ $message }}</p> @enderror
            <button type="button" wire:click="goToStep3" wire:loading.attr="disabled" wire:target="goToStep3"
                class="w-full bg-trenakt-primary text-white rounded-md py-2.5 font-medium hover:opacity-90 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="goToStep3">Continue</span>
                <span wire:loading wire:target="goToStep3">Please wait...</span>
            </button>
        </div>
    </div>

    <div x-show="step === 3" x-cloak>
        <h2 class="text-lg font-semibold mb-6">Where are you located?</h2>
        <div class="space-y-3">
            @foreach ($countries as $country)
                <button type="button" @click="country_id = {{ $country->id }}; $wire.country_id = {{ $country->id }}"
                    class="w-full text-left border rounded-lg p-4 transition"
                    :class="country_id === {{ $country->id }} ? 'border-trenakt-primary bg-trenakt-bg' : 'border-gray-200 hover:border-gray-300'">
                    {{ $country->name }}
                </button>
            @endforeach
        </div>
        @error('country_id') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror

        <label class="flex items-start gap-2 mt-6 cursor-pointer select-none" x-data="{ agreed: false }">
            <span class="relative inline-flex items-center justify-center w-4.5 h-4.5 rounded border mt-0.5 shrink-0 transition"
                @click="agreed = !agreed; $wire.agreed_terms = agreed"
                :class="agreed ? 'bg-trenakt-primary border-trenakt-primary' : 'bg-white border-gray-300'">
                <x-icon name="check" class="w-3 h-3 text-white" x-show="agreed" x-cloak />
            </span>
            <span class="text-xs text-gray-500">
                By signing up, you agree to our
                <a href="/terms" wire:navigate class="text-trenakt-primary hover:underline">Terms of Service</a>
                and
                <a href="/privacy" wire:navigate class="text-trenakt-primary hover:underline">Privacy Policy</a>.
            </span>
        </label>
        @error('agreed_terms') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

        <button type="button" wire:click="register" wire:loading.attr="disabled" wire:target="register"
            class="w-full mt-4 bg-trenakt-primary text-white rounded-md py-2.5 font-medium hover:opacity-90 transition disabled:opacity-60">
            <span wire:loading.remove wire:target="register">Create account</span>
            <span wire:loading wire:target="register">Creating account...</span>
        </button>
    </div>

    <p class="text-sm text-gray-500 text-center mt-6">
        Already have an account?
        <a href="/login" wire:navigate class="text-trenakt-primary font-medium hover:underline">Log in</a>
    </p>
</div>