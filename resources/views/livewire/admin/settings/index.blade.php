<div class="w-full max-w-7xl mx-auto lg:flex lg:items-start lg:gap-8">
<div class="w-full lg:flex-1 lg:max-w-2xl">
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

            <button type="button" wire:click="$set('verificationMode', 'human')"
                class="w-full flex items-center justify-between border rounded-md px-3 py-2.5 transition {{ $verificationMode === 'human' ? 'border-trenakt-accent ring-1 ring-trenakt-accent' : 'border-gray-200 dark:border-white/10' }}">
                <span class="text-sm">Human review</span>
                @if ($verificationMode === 'human')
                    <span class="text-[10px] uppercase tracking-wide bg-trenakt-success-light text-trenakt-success rounded-full px-2 py-0.5">Active</span>
                @endif
            </button>
            <button type="button" wire:click="$set('verificationMode', 'ai')"
                class="w-full flex items-center justify-between border rounded-md px-3 py-2.5 mt-2 transition {{ $verificationMode === 'ai' ? 'border-trenakt-accent ring-1 ring-trenakt-accent' : 'border-gray-200 dark:border-white/10' }}">
                <span class="text-sm">AI-assisted review</span>
                @if ($verificationMode === 'ai')
                    <span class="text-[10px] uppercase tracking-wide bg-trenakt-success-light text-trenakt-success rounded-full px-2 py-0.5">Active</span>
                @endif
            </button>

            @if ($verificationMode === 'ai')
                <div class="mt-3 space-y-3 border border-gray-200 dark:border-white/10 rounded-md p-3.5">
                    <p class="text-xs text-gray-400 dark:text-white/40">
                        Every submission still goes through deterministic checks first (required evidence present, links well-formed). AI only evaluates evidence that passes those, and any submission it isn't confident about is left for human review exactly as it is today. You'll only be notified about the ones AI actually couldn't resolve - not every submission that comes in.
                    </p>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-white/50">OpenAI API key</label>
                        <input wire:model="openaiApiKey" type="password" autocomplete="off"
                            placeholder="{{ $openaiKeyConfigured ? 'Configured - leave blank to keep it' : 'sk-...' }}"
                            class="w-full mt-1 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-accent">
                        @error('openaiApiKey') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-400 dark:text-white/40 mt-1">
                            @if ($openaiKeyConfigured)
                                <span class="text-trenakt-success">A key is configured.</span> Stored encrypted - never shown again. Enter a new one to replace it.
                            @else
                                <span class="text-trenakt-warning">No key configured yet.</span> AI review fails safe to human review until one is set.
                            @endif
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-white/50">Auto-approve above</label>
                            <input wire:model="aiAutoApproveThreshold" type="number" min="0" max="1" step="0.01"
                                class="w-full mt-1 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-accent">
                            @error('aiAutoApproveThreshold') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-white/50">Auto-reject above</label>
                            <input wire:model="aiAutoRejectThreshold" type="number" min="0" max="1" step="0.01"
                                class="w-full mt-1 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-accent">
                            @error('aiAutoRejectThreshold') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-white/40">Confidence scores (0 to 1). Anything in between, or anything the AI isn't sure about, is left for human review.</p>

                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-white/50">Reason to use for AI-rejected submissions</label>
                        <x-select model="aiRejectionReasonId" :options="collect(['' => '— Never auto-reject, send to human review instead —'])->merge($rejectionReasons->pluck('label', 'id'))->all()" :selected="$aiRejectionReasonId" placeholder="— Never auto-reject, send to human review instead —" />
                        @error('aiRejectionReasonId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif
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

{{-- Plain-language explainer for every setting on this page, one card per
     section in the same order as the form. Sits to the right of the form on
     large screens (sticky, so it stays visible while scrolling) and stacks
     below it on smaller screens - it's reference material, not the primary
     action, so it never gets priority over the form itself.

     Both this and the form column above use lg:flex-1 (grow to fill
     whatever width the admin main content area actually has, up to their
     own max-w cap) rather than a fixed width - a fixed-width pair left a
     large empty gutter on the right on wide screens, since nothing was
     claiming the rest of the row. --}}
<aside class="w-full lg:flex-1 lg:max-w-xl mt-8 lg:mt-0 lg:sticky lg:top-24 space-y-4">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Guide</p>
        <h2 class="text-sm font-bold">How these settings work</h2>
    </div>

    <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
        <p class="text-xs font-semibold mb-1">Payout day &amp; Always open</p>
        <p class="text-xs text-gray-400 dark:text-white/40">
            Withdrawal requests only actually get paid out once a week, on whichever day you pick. Anything requested on another day just waits until the next payout day. Turn "Always open" on to skip that wait entirely and pay out any day instead - off is the safer, more predictable default.
        </p>
    </div>

    <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
        <p class="text-xs font-semibold mb-1">Submission verification</p>
        <p class="text-xs text-gray-400 dark:text-white/40 mb-2">
            Controls who checks a participant's task proof before they get paid.
        </p>
        <p class="text-xs text-gray-400 dark:text-white/40 mb-2">
            <span class="font-medium text-trenakt-dark dark:text-white">Human review</span> - every submission goes straight to your Submissions queue. Nothing is decided automatically.
        </p>
        <p class="text-xs text-gray-400 dark:text-white/40 mb-2">
            <span class="font-medium text-trenakt-dark dark:text-white">AI-assisted review</span> - obvious, clear-cut submissions get approved or rejected automatically; anything unclear, or anything that goes wrong, still lands in your queue exactly like human review does.
        </p>
        <ul class="text-xs text-gray-400 dark:text-white/40 list-disc pl-4 space-y-1.5">
            <li><span class="font-medium text-trenakt-dark dark:text-white">OpenAI API key</span> - required for AI mode to actually work. Stored encrypted, never shown again once saved. No key means AI mode safely falls back to human review for everything.</li>
            <li><span class="font-medium text-trenakt-dark dark:text-white">Auto-approve / auto-reject thresholds</span> - how confident AI needs to be (0 = not at all, 1 = certain) before it's allowed to decide on its own. Anything below either number is left for you.</li>
            <li><span class="font-medium text-trenakt-dark dark:text-white">AI rejection reason</span> - AI will only ever auto-reject using this reason. Leave it unset and AI can still auto-approve, but every rejection is left for a human to decide.</li>
            <li>Deterministic problems (a required field left empty, a broken link) always block auto-approval, no matter how confident AI is about anything else.</li>
            <li>You're only notified when AI genuinely couldn't decide - not for every submission it successfully handles on its own.</li>
        </ul>
    </div>

    <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
        <p class="text-xs font-semibold mb-1">Account activation fee</p>
        <p class="text-xs text-gray-400 dark:text-white/40">
            A one-time fee participants must pay before they can accept or submit tasks. "Let existing participants keep free access" decides whether that fee applies only to people who join after you turn it on, or to everyone unpaid right now - including people who joined before the fee existed.
        </p>
    </div>

    <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
        <p class="text-xs font-semibold mb-1">Referral reward</p>
        <p class="text-xs text-gray-400 dark:text-white/40">
            Paid into a participant's wallet the moment someone they referred pays the activation fee above. This only matters once that fee is switched on - with no activation fee, there's nothing to trigger a referral reward.
        </p>
    </div>
</aside>
</div>
