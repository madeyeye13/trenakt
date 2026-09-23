<div>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Platform</p>
        <h1 class="text-2xl font-bold">Countries</h1>
        <p class="text-sm text-gray-500 dark:text-white/50 mt-1">Which countries can register, and which can withdraw their earnings, and through which gateway account.</p>
    </div>

    @if ($countries->isEmpty())
        <x-empty-state icon="shield" title="No countries yet" description="Countries are set up when the platform launches. Add one to your database to get started." />
    @else
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($countries as $country)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0 flex items-center gap-3">
                            <div>
                                <p class="text-sm font-medium">{{ $country->name }} <span class="text-xs text-gray-400 dark:text-white/40">({{ $country->iso_code }} &middot; {{ $country->currency_code }})</span></p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $country->is_active ? 'bg-trenakt-success-light text-trenakt-success' : 'bg-gray-100 dark:bg-white/5 text-gray-400' }}">
                                        {{ $country->is_active ? 'Open for registration' : 'Registration closed' }}
                                    </span>
                                    <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $country->supportsPayout() ? 'bg-trenakt-success-light text-trenakt-success' : 'bg-trenakt-warning-light text-trenakt-warning' }}">
                                        {{ $country->supportsPayout() ? 'Withdrawals live' : 'Withdrawals not set up' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button type="button" wire:click="edit({{ $country->id }})"
                            @click="$dispatch('open-modal', { name: 'country-payout-form' })"
                            class="text-xs font-medium text-trenakt-accent hover:underline shrink-0">
                            Configure
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <x-modal name="country-payout-form" maxWidth="md">
        <div wire:loading.block wire:target="edit" class="animate-pulse space-y-5">
            <div class="h-5 bg-gray-200 dark:bg-white/10 rounded w-2/5 mb-4"></div>
            <div class="flex items-center justify-between">
                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/3"></div>
                <div class="h-5 w-9 bg-gray-200 dark:bg-white/10 rounded-full"></div>
            </div>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-white/10">
                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/4"></div>
                <div class="h-5 w-9 bg-gray-200 dark:bg-white/10 rounded-full"></div>
            </div>
            <div class="h-10 bg-gray-200 dark:bg-white/10 rounded"></div>
            <div class="h-10 bg-gray-200 dark:bg-white/10 rounded"></div>
            <div class="h-10 bg-gray-200 dark:bg-white/10 rounded"></div>
            <div class="h-10 bg-gray-200 dark:bg-white/10 rounded w-1/3 ml-auto"></div>
        </div>

        <div wire:loading.remove wire:target="edit">
            <h3 class="text-lg font-semibold mb-4">Configure country</h3>

            <div class="space-y-5">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-300">Open for registration</span>
                    <x-toggle model="isActive" :checked="$isActive" />
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold">Withdrawals</p>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">Turn on once a working API key is set below.</p>
                        </div>
                        <x-toggle model="payoutEnabled" :checked="$payoutEnabled" />
                    </div>
                    @error('payoutEnabled') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Gateway</label>
                    <x-select model="payoutProvider" :options="['paystack' => 'Paystack']" :selected="$payoutProvider" />
                    @error('payoutProvider') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Gateway's country name</label>
                    <input wire:model="payoutCountrySlug" type="text" placeholder="e.g. ghana"
                        class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1.5">The value Paystack itself uses for this country (lowercase, e.g. "nigeria", "ghana", "kenya").</p>
                    @error('payoutCountrySlug') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">API secret key</label>
                    <input wire:model="payoutSecretKey" type="password" autocomplete="off" placeholder="{{ $hasSavedKey ? 'Key saved, leave blank to keep it' : 'sk_live_...' }}"
                        class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1.5">This country's own Paystack secret key, never the Nigeria one. Stored encrypted; leave blank to keep the key already saved.</p>
                    @error('payoutSecretKey') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Payout methods</label>
                    <div class="space-y-2">
                        <label class="flex items-center justify-between border border-gray-200 dark:border-white/10 rounded-md px-3 py-2.5 cursor-pointer">
                            <span class="text-sm">Bank transfer</span>
                            <x-toggle model="methodBank" :checked="$methodBank" />
                        </label>
                        <label class="flex items-center justify-between border border-gray-200 dark:border-white/10 rounded-md px-3 py-2.5 cursor-pointer">
                            <span class="text-sm">Mobile money (MTN, Vodafone, AirtelTigo, etc.)</span>
                            <x-toggle model="methodMobileMoney" :checked="$methodMobileMoney" />
                        </label>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1.5">Test a small real withdrawal after enabling a new method before relying on it. Gateway support varies by country.</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
                <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                    class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Save</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </div>
    </x-modal>
</div>
