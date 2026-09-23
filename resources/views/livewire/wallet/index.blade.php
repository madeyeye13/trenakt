<div>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Wallet</p>
        <h1 class="text-2xl font-bold">Your balance</h1>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                    <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Available</p>
                    <p class="text-xl sm:text-2xl font-bold text-trenakt-primary"><x-currency :amount="$balance" /></p>
                </div>
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                    <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Held for campaigns</p>
                    <p class="text-xl sm:text-2xl font-bold text-trenakt-dark dark:text-white"><x-currency :amount="$reserved" /></p>
                </div>
            </div>

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5" x-data="{ gateway: @entangle('gateway') }">
                <h2 class="text-sm font-semibold">Fund wallet</h2>

                <div>
                    <label class="text-sm font-medium">Amount (NGN)</label>
                    <input wire:model="amount" type="number" min="100" step="100"
                        class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                    @error('amount') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror

                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach ([2000, 5000, 10000, 20000, 50000] as $preset)
                            <button type="button" wire:click="$set('amount', {{ $preset }})"
                                class="text-xs font-medium border border-gray-200 dark:border-white/10 rounded-full px-3 py-1 text-gray-500 dark:text-gray-400 hover:border-trenakt-primary hover:text-trenakt-primary transition">
                                ₦{{ number_format($preset) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium mb-1.5 block">Pay with</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="gateway = 'paystack'"
                            class="border rounded-md px-4 py-3 text-sm font-medium text-left transition"
                            :class="gateway === 'paystack' ? 'border-trenakt-primary bg-trenakt-primary/5 text-trenakt-primary' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400'">
                            Paystack
                        </button>
                        <button type="button" @click="gateway = 'flutterwave'"
                            class="border rounded-md px-4 py-3 text-sm font-medium text-left transition"
                            :class="gateway === 'flutterwave' ? 'border-trenakt-primary bg-trenakt-primary/5 text-trenakt-primary' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400'">
                            Flutterwave
                        </button>
                    </div>
                </div>

                <button type="button" wire:click="fund" wire:loading.attr="disabled" wire:target="fund"
                    class="w-full bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2.5 disabled:opacity-60">
                    <span wire:loading.remove wire:target="fund">Continue to payment</span>
                    <span wire:loading wire:target="fund">Connecting...</span>
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 lg:sticky lg:top-6 self-start">
            <h2 class="text-sm font-semibold mb-4">Recent activity</h2>

            @if ($transactions->isEmpty())
                <x-empty-state icon="wallet" title="No activity yet" description="Your wallet transactions will show up here." />
            @else
                <div class="space-y-3">
                    @foreach ($transactions as $txn)
                        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-100 dark:border-white/10 last:border-0">
                            <div>
                                <p class="font-medium text-trenakt-dark dark:text-white">{{ ucfirst(str_replace('_', ' ', $txn->type)) }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40">{{ $txn->created_at->format('M j, Y g:ia') }}</p>
                            </div>
                            <span class="font-medium {{ str_contains($txn->type, 'failed') ? 'text-trenakt-danger' : (in_array($txn->type, ['wallet_funded', 'campaign_reservation_released', 'earning_released', 'withdrawal_successful']) ? 'text-trenakt-primary' : 'text-trenakt-dark dark:text-white') }}">
                                <x-currency :amount="$txn->amount" />
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
