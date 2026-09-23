@php
    $bankOptions = collect($banks)->pluck('name', 'code')->toArray();
@endphp

<div>
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Earning</p>
        <h1 class="text-2xl font-bold">Earnings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your balance from approved tasks, and withdrawals.</p>
    </div>

    @if (! $profileComplete)
        <div class="flex items-start gap-3 bg-trenakt-warning-light text-trenakt-warning rounded-lg p-4 mb-6">
            <x-icon name="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
            <div>
                <p class="text-sm font-semibold">Finish setting up your profile</p>
                <p class="text-sm mt-0.5">You'll need a complete profile and a verified bank account before you can withdraw.
                    <a href="{{ route('profile.edit') }}" wire:navigate class="underline font-medium">Go to profile</a>
                </p>
            </div>
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6 min-w-0">
        <div class="lg:col-span-2 min-w-0 space-y-6">
            <div class="grid sm:grid-cols-2 gap-4 min-w-0">
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 min-w-0">
                    <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Available balance</p>
                    <p class="text-xl sm:text-2xl font-bold text-trenakt-primary"><x-currency :amount="$availableBalance" /></p>
                    @if ($referralEarnings > 0)
                        <p class="text-xs text-gray-400 dark:text-white/40 mt-1">includes <x-currency :amount="$referralEarnings" /> from referrals</p>
                    @endif
                </div>
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 min-w-0">
                    <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Pending withdrawal</p>
                    <p class="text-xl sm:text-2xl font-bold"><x-currency :amount="$reservedBalance" /></p>
                </div>
            </div>

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
                    <p class="text-sm font-semibold">Recent activity</p>
                </div>

                @if ($transactions->isEmpty())
                    <x-empty-state icon="wallet" title="No activity yet" description="Approved tasks and withdrawals will show up here." />
                @else
                    <div class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($transactions as $transaction)
                            @php
                                $isCredit = in_array($transaction->type, ['participant_reward_earned', 'referral_reward_earned', 'manual_admin_adjustment']);
                                $label = match ($transaction->type) {
                                    'participant_reward_earned' => 'Task reward',
                                    'referral_reward_earned' => 'Referral reward',
                                    'withdrawal_requested' => 'Withdrawal requested',
                                    'withdrawal_successful' => 'Withdrawal paid',
                                    'withdrawal_failed' => 'Withdrawal failed (refunded)',
                                    'manual_admin_adjustment' => 'Balance adjustment',
                                    default => ucfirst(str_replace('_', ' ', $transaction->type)),
                                };
                            @endphp
                            <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $label }}</p>
                                    <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">{{ $transaction->created_at->format('M j, Y g:ia') }}</p>
                                </div>
                                <span class="text-sm font-semibold shrink-0 {{ $isCredit ? 'text-trenakt-success' : 'text-trenakt-dark dark:text-white' }}">
                                    {{ $isCredit ? '+' : '-' }}<x-currency :amount="$transaction->amount" />
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="lg:sticky lg:top-6 self-start min-w-0 space-y-6">
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-4">Withdraw</p>

                @if (! $payoutSupported)
                    <button type="button" disabled
                        class="w-full bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-white/30 text-sm font-semibold rounded-md px-4 py-2.5 cursor-not-allowed">
                        Request withdrawal
                    </button>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-3">Withdrawals for your country aren't available yet. We're working on it.</p>
                @elseif ($profileComplete && $payoutWindowOpen)
                    <button type="button" wire:click="openWithdraw"
                        class="w-full bg-trenakt-primary text-white text-sm font-semibold rounded-md px-4 py-2.5 hover:opacity-90 transition">
                        Request withdrawal
                    </button>
                @else
                    <button type="button" disabled
                        class="w-full bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-white/30 text-sm font-semibold rounded-md px-4 py-2.5 cursor-not-allowed">
                        Request withdrawal
                    </button>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-3">
                        @if (! $profileComplete)
                            Complete your profile first.
                        @else
                            Withdrawals open on {{ $payoutDay }}s.
                        @endif
                    </p>
                @endif

                <p class="text-xs text-gray-400 dark:text-white/40 mt-3">
                    Withdrawals are paid out on {{ $payoutDay }}s and can take up to 24 hours to land.
                </p>
            </div>

            @if ($pendingWithdrawals->isNotEmpty())
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-3">In progress</p>
                    <div class="space-y-3">
                        @foreach ($pendingWithdrawals as $withdrawal)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ ucfirst($withdrawal->status) }}</span>
                                <span class="font-medium"><x-currency :amount="$withdrawal->amount" /></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($referralSystemEnabled)
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Refer & earn</p>
                    <p class="text-xs text-gray-400 dark:text-white/40 mb-3">Earned so far: <span class="font-semibold text-trenakt-primary"><x-currency :amount="$referralEarnings" /></span></p>

                    <div x-data="{
                        copied: false,
                        async copyLink() {
                            const text = '{{ $referralLink }}';
                            try {
                                if (navigator.clipboard && window.isSecureContext) {
                                    await navigator.clipboard.writeText(text);
                                } else {
                                    const el = document.createElement('textarea');
                                    el.value = text;
                                    el.style.position = 'fixed';
                                    el.style.opacity = '0';
                                    document.body.appendChild(el);
                                    el.focus();
                                    el.select();
                                    document.execCommand('copy');
                                    document.body.removeChild(el);
                                }
                            } catch (e) {}
                            this.copied = true;
                            setTimeout(() => this.copied = false, 1500);
                        }
                    }">
                        <div class="flex items-center gap-2 border border-gray-200 dark:border-white/10 rounded-md px-3 py-2">
                            <span class="text-xs truncate flex-1 min-w-0">{{ $referralLink }}</span>
                            <button type="button" @click="copyLink()"
                                class="text-xs font-medium text-trenakt-primary shrink-0">
                                <span x-show="!copied">Copy</span>
                                <span x-show="copied" x-cloak>Copied!</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-white/40 mt-2">Or share your code: <span class="font-mono font-semibold text-trenakt-dark dark:text-white">{{ $referralCode }}</span></p>
                    </div>

                    @if ($referrals->isNotEmpty())
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-white/10 space-y-2">
                            @foreach ($referrals as $referred)
                                @php $paid = $referred->activationPayments->isNotEmpty(); @endphp
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500 dark:text-gray-400 truncate flex-1 min-w-0">{{ $referred->name }}</span>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 {{ $paid ? 'bg-trenakt-success-light text-trenakt-success' : 'bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-white/40' }}">
                                        {{ $paid ? 'Earned' : 'Pending' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Step 1: add & verify a payout account (only shown the first time) --}}
    <x-modal name="add-bank-account" maxWidth="md">
        <h3 class="text-lg font-semibold mb-1">{{ $method === 'mobile_money' ? 'Add your mobile money account' : 'Add your bank account' }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">We'll verify this matches your profile name before saving it. We never ask for ID.</p>

        <div class="space-y-4">
            @if (count($payoutMethods) > 1)
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Payout method</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" wire:click="$set('method', 'bank')"
                            class="text-sm font-medium border rounded-md px-3 py-2 transition {{ $method === 'bank' ? 'border-trenakt-primary bg-trenakt-primary/5 text-trenakt-primary' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400' }}">
                            Bank account
                        </button>
                        <button type="button" wire:click="$set('method', 'mobile_money')"
                            class="text-sm font-medium border rounded-md px-3 py-2 transition {{ $method === 'mobile_money' ? 'border-trenakt-primary bg-trenakt-primary/5 text-trenakt-primary' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400' }}">
                            Mobile money
                        </button>
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ $method === 'mobile_money' ? 'Mobile network' : 'Bank' }}</label>
                <x-select model="bankCode" :options="$bankOptions" :selected="$bankCode" :placeholder="$method === 'mobile_money' ? 'Select your network' : 'Select your bank'" />
                @error('bankCode') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">{{ $method === 'mobile_money' ? 'Mobile money number' : 'Account number' }}</label>
                <input wire:model="accountNumber" type="text" inputmode="numeric" maxlength="20" placeholder="{{ $method === 'mobile_money' ? 'e.g. 0244xxxxxx' : 'Your account number' }}"
                    class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-primary">
                @error('accountNumber') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
            </div>

            @if ($resolvedAccountName)
                @if ($nameMismatch)
                    <div class="flex items-start gap-3 bg-trenakt-danger/10 text-trenakt-danger rounded-lg p-3.5">
                        <x-icon name="alert-triangle" class="w-4.5 h-4.5 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-xs font-semibold">Name doesn't match your profile</p>
                            <p class="text-xs mt-0.5">This account belongs to "{{ $resolvedAccountName }}", which doesn't match your profile name. Update your profile name to match, or use a different account.</p>
                            <a href="{{ route('profile.edit') }}" wire:navigate class="text-xs font-medium underline mt-1.5 inline-block">Edit profile name</a>
                        </div>
                    </div>
                @else
                    <div class="flex items-start gap-3 bg-trenakt-success-light text-trenakt-success rounded-lg p-3.5">
                        <x-icon name="check-circle" class="w-4.5 h-4.5 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-xs font-semibold">Account verified</p>
                            <p class="text-xs mt-0.5">{{ $resolvedAccountName }} at {{ $bankName }}. Confirm to save this account.</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>

            @if (! $resolvedAccountName || $nameMismatch)
                <button type="button" wire:click="resolveAccount" wire:loading.attr="disabled" wire:target="resolveAccount"
                    class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="resolveAccount">Verify account</span>
                    <span wire:loading wire:target="resolveAccount">Verifying...</span>
                </button>
            @else
                <button type="button" wire:click="saveBankAccount" wire:loading.attr="disabled" wire:target="saveBankAccount"
                    class="bg-trenakt-success text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="saveBankAccount">Confirm and save</span>
                    <span wire:loading wire:target="saveBankAccount">Saving...</span>
                </button>
            @endif
        </div>
    </x-modal>

    {{-- Step 2: request withdrawal amount --}}
    <x-modal name="request-withdrawal" maxWidth="sm">
        <h3 class="text-lg font-semibold mb-2">Request a withdrawal</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Available balance: <span class="font-medium text-trenakt-dark dark:text-white"><x-currency :amount="$availableBalance" /></span>
        </p>

        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Amount</label>
        <input wire:model="amount" type="number" step="0.01" min="100" placeholder="0.00"
            class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-primary">
        @error('amount') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror

        <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="$dispatch('close-modal')" wire:loading.attr="disabled" wire:target="requestWithdrawal" class="text-sm font-medium text-gray-500">Cancel</button>
            <button type="button" wire:click="requestWithdrawal" wire:loading.attr="disabled" wire:target="requestWithdrawal"
                class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                <span wire:loading.remove wire:target="requestWithdrawal">Request withdrawal</span>
                <span wire:loading wire:target="requestWithdrawal">Requesting...</span>
            </button>
        </div>
    </x-modal>
</div>
