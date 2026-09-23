<div class="max-w-xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Platform</p>
        <h1 class="text-2xl font-bold">Settings</h1>
    </div>

    <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 space-y-6">
        <div>
            <p class="text-sm font-semibold mb-1">Payout day</p>
            <p class="text-xs text-gray-400 dark:text-white/40 mb-3">Withdrawal requests are only paid out on this day each week.</p>
            <x-select model="payoutDay" :options="[
                'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday',
                'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday',
            ]" :selected="$payoutDay" />
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-white/10">
            <div>
                <p class="text-sm font-semibold">Always open</p>
                <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">Let participants withdraw any day, ignoring the payout day above.</p>
            </div>
            <x-toggle model="withdrawalAlwaysOpen" :checked="$withdrawalAlwaysOpen" />
        </div>

        <div class="pt-2 border-t border-gray-100 dark:border-white/10">
            <p class="text-sm font-semibold mb-1">Submission verification</p>
            <p class="text-xs text-gray-400 dark:text-white/40 mb-3">How task submissions get checked before an admin can approve them.</p>
            <div class="flex items-center justify-between border border-gray-200 dark:border-white/10 rounded-md px-3 py-2.5">
                <span class="text-sm">Human review</span>
                <span class="text-[10px] uppercase tracking-wide bg-trenakt-success-light text-trenakt-success rounded-full px-2 py-0.5">Active</span>
            </div>
            <div class="flex items-center justify-between border border-gray-200 dark:border-white/10 rounded-md px-3 py-2.5 mt-2 opacity-50">
                <span class="text-sm">AI-assisted review</span>
                <span class="text-[10px] uppercase tracking-wide bg-gray-100 dark:bg-white/5 text-gray-400 rounded-full px-2 py-0.5">Coming soon</span>
            </div>
        </div>

        <div class="pt-2 border-t border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold">Account activation fee</p>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">A one-time fee earning participants must pay before they can accept or submit tasks.</p>
                </div>
                <x-toggle model="activationFeeEnabled" :checked="$activationFeeEnabled" />
            </div>

            @if ($activationFeeEnabled)
                <div class="mt-3 space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-white/50">Fee amount (₦)</label>
                        <input wire:model="activationFeeAmount" type="number" min="0" step="0.01"
                            class="w-full mt-1 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-accent">
                        @error('activationFeeAmount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between border border-gray-200 dark:border-white/10 rounded-md px-3 py-2.5">
                        <div>
                            <p class="text-sm">Let existing participants keep free access</p>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">Off means every unpaid participant is asked, including ones who registered before today.</p>
                        </div>
                        <x-toggle model="activationFeeGrandfatherExisting" :checked="$activationFeeGrandfatherExisting" />
                    </div>
                </div>
            @endif
        </div>

        <div class="pt-2 border-t border-gray-100 dark:border-white/10">
            <p class="text-sm font-semibold mb-1">Referral reward</p>
            <p class="text-xs text-gray-400 dark:text-white/40 mb-3">Paid to a participant's wallet when someone they referred pays their activation fee. The referral field and link only appear once the fee above is on.</p>
            <label class="text-xs font-medium text-gray-500 dark:text-white/50">Reward amount (₦)</label>
            <input wire:model="referralRewardAmount" type="number" min="0" step="0.01"
                class="w-full mt-1 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-accent">
            @error('referralRewardAmount') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end pt-2">
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save settings</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>
</div>
