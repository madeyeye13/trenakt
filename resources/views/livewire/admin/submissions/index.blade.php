<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Earning</p>
            <h1 class="text-2xl font-bold">Task submissions</h1>
            <p class="text-sm text-gray-500 dark:text-white/50 mt-1">Review what participants submit and release their reward.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach ([
            'submitted' => 'Awaiting review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'all' => 'All',
        ] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')"
                class="text-left bg-white dark:bg-trenakt-surface-dark border rounded-lg p-4 transition {{ $status === $key ? 'border-trenakt-accent ring-1 ring-trenakt-accent' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20' }}">
                <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ $label }}</p>
                <p class="text-xl font-bold mt-1">{{ $counts[$key] }}</p>
            </button>
        @endforeach
    </div>

    <div class="relative mb-5">
        <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by participant or campaign"
            class="w-full sm:w-96 border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
    </div>

    <div wire:loading.remove wire:target="status,search,page" class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
        @if ($submissions->isEmpty())
            <x-empty-state icon="check-circle" title="Nothing here" description="No submissions match this filter." />
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($submissions as $submission)
                    @php
                        $statusClass = match (true) {
                            $submission->status === 'approved' && $submission->reward_revoked_at => 'bg-trenakt-danger/10 text-trenakt-danger',
                            $submission->status === 'approved' => 'bg-trenakt-success-light text-trenakt-success',
                            $submission->status === 'rejected' => 'bg-trenakt-danger/10 text-trenakt-danger',
                            default => 'bg-trenakt-warning-light text-trenakt-warning',
                        };
                        $statusLabel = $submission->status === 'approved' && $submission->reward_revoked_at ? 'Revoked' : ucfirst($submission->status);
                    @endphp
                    <button type="button" wire:click="showSubmission({{ $submission->id }})" class="w-full text-left px-5 py-4 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $submission->campaign->title ?? 'Deleted campaign' }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40 mt-1">
                                    {{ $submission->participant->name ?? 'Deleted user' }} &middot; {{ $submission->submitted_at->format('M j, Y g:ia') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ $statusLabel }}</span>
                                <x-icon name="eye" class="w-4 h-4 text-gray-400" />
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
            <div class="p-5">{{ $submissions->links() }}</div>
        @endif
    </div>

    <div wire:loading.flex wire:target="status,search,page" class="flex flex-col gap-3">
        @for ($i = 0; $i < 5; $i++)
            <x-skeleton-card />
        @endfor
    </div>

    <x-modal name="submission-details" maxWidth="lg">
        @if ($selectedSubmission)
            <div class="flex items-start justify-between gap-4 mb-5">
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 dark:text-white/40">{{ $selectedSubmission->campaign->title ?? 'Deleted campaign' }}</p>
                    <h2 class="text-lg font-semibold mt-1">{{ $selectedSubmission->participant->name ?? 'Deleted user' }}</h2>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1">{{ $selectedSubmission->participant->email ?? '' }}</p>
                </div>
                <button type="button" @click="$dispatch('close-modal')" class="text-gray-400 hover:text-trenakt-dark dark:hover:text-white shrink-0" title="Close">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <div class="space-y-4 max-h-[50vh] overflow-y-auto pr-1 mb-5 scrollbar-brand">
                @foreach ($participantFields as $field)
                    <div class="border-b border-gray-100 dark:border-white/10 pb-4 last:border-0">
                        <p class="text-xs text-gray-400 dark:text-white/40 mb-1">{{ $field->label }}</p>
                        @if ($field->type === 'file' && data_get($selectedSubmission->answers, (string) $field->id))
                            <a href="{{ asset('storage/' . data_get($selectedSubmission->answers, (string) $field->id)) }}" target="_blank" class="text-sm text-trenakt-accent hover:underline">View uploaded file</a>
                        @else
                            <p class="text-sm whitespace-pre-wrap">{{ data_get($selectedSubmission->answers, (string) $field->id, 'No answer provided') }}</p>
                        @endif
                    </div>
                @endforeach

                @foreach ($customFields as $field)
                    <div class="border-b border-gray-100 dark:border-white/10 pb-4 last:border-0">
                        <p class="text-xs text-gray-400 dark:text-white/40 mb-1">{{ $field->label }}</p>
                        @if ($field->type === 'file' && data_get($selectedSubmission->answers, $field->field_key))
                            <a href="{{ asset('storage/' . data_get($selectedSubmission->answers, $field->field_key)) }}" target="_blank" class="text-sm text-trenakt-accent hover:underline">View uploaded file</a>
                        @else
                            <p class="text-sm whitespace-pre-wrap">{{ data_get($selectedSubmission->answers, $field->field_key, 'No answer provided') }}</p>
                        @endif
                    </div>
                @endforeach

                @if ($participantFields->isEmpty() && $customFields->isEmpty())
                    <x-empty-state icon="info-circle" title="No submitted fields" description="This campaign has no participant requirements." />
                @endif
            </div>

            @if ($selectedSubmission->latestVerification)
                @php $verification = $selectedSubmission->latestVerification; @endphp
                <div class="mb-5 rounded-lg border border-gray-200 dark:border-white/10 p-3.5">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-white/40">AI check</p>
                        @php
                            $verdictClass = match ($verification->verdict) {
                                'approved' => 'bg-trenakt-success-light text-trenakt-success',
                                'rejected' => 'bg-trenakt-danger/10 text-trenakt-danger',
                                default => 'bg-trenakt-warning-light text-trenakt-warning',
                            };
                        @endphp
                        <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $verdictClass }}">{{ str_replace('_', ' ', $verification->verdict) }}</span>
                    </div>
                    @if ($verification->confidence !== null)
                        <p class="text-xs text-gray-400 dark:text-white/40 mb-1.5">Confidence: {{ number_format($verification->confidence * 100, 0) }}%</p>
                    @endif
                    @if ($verification->error)
                        <p class="text-xs text-trenakt-danger">{{ $verification->error }}</p>
                    @elseif (! empty($verification->reasons))
                        <ul class="text-xs text-gray-500 dark:text-gray-400 list-disc pl-4 space-y-0.5">
                            @foreach ($verification->reasons as $reason)
                                <li>{{ $reason }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="text-[10px] text-gray-400 dark:text-white/30 mt-2">This is informational only - approve or reject below is always the final decision.</p>
                </div>
            @endif

            @if ($selectedSubmission->status === 'submitted')
                <div x-data="{ rejecting: false }">
                    <div x-show="!rejecting" class="flex justify-end gap-3">
                        <button type="button" @click="rejecting = true" wire:loading.attr="disabled" wire:target="approve"
                            class="inline-flex items-center gap-1.5 bg-trenakt-danger/10 text-trenakt-danger text-sm font-semibold rounded-md px-4 py-2.5 hover:opacity-80 transition">
                            <x-icon name="x" class="w-4 h-4" />
                            Reject
                        </button>
                        <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve"
                            class="inline-flex items-center gap-1.5 bg-trenakt-success text-white text-sm font-semibold rounded-md px-4 py-2.5 hover:opacity-90 transition disabled:opacity-60">
                            <x-icon name="check" class="w-4 h-4" wire:loading.remove wire:target="approve" />
                            <span wire:loading.remove wire:target="approve">Approve &amp; pay ₦{{ number_format($selectedSubmission->campaign->rate_per_participant ?? 0, 2) }}</span>
                            <span wire:loading wire:target="approve">Approving...</span>
                        </button>
                    </div>

                    <div x-show="rejecting" x-cloak>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Why is this being rejected?</p>
                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach ($reasons as $reason)
                                <button type="button" wire:click="$set('selectedReasonId', {{ $reason->id }})"
                                    class="text-xs font-medium rounded-full px-3 py-1.5 border transition {{ $selectedReasonId === $reason->id ? 'border-trenakt-danger bg-trenakt-danger/10 text-trenakt-danger' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400 hover:border-gray-300' }}">
                                    {{ $reason->label }}
                                </button>
                            @endforeach
                        </div>
                        @error('selectedReasonId') <p class="text-xs text-trenakt-danger mb-3">{{ $message }}</p> @enderror

                        <textarea wire:model="rejectNote" rows="2" placeholder="Add more detail for the participant (optional)"
                            class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-danger mb-3"></textarea>

                        <div class="flex justify-end gap-3">
                            <button type="button" @click="rejecting = false" wire:loading.attr="disabled" wire:target="reject" class="text-sm font-medium text-gray-500">Cancel</button>
                            <button type="button" wire:click="reject" wire:loading.attr="disabled" wire:target="reject"
                                class="bg-trenakt-danger text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                                <span wire:loading.remove wire:target="reject">Reject submission</span>
                                <span wire:loading wire:target="reject">Rejecting...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @elseif ($selectedSubmission->status === 'approved')
                @if ($selectedSubmission->reward_revoked_at)
                    <div class="flex items-start gap-3 bg-trenakt-danger/10 text-trenakt-danger rounded-lg p-3.5">
                        <x-icon name="alert-triangle" class="w-4.5 h-4.5 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm font-medium">Reward revoked {{ $selectedSubmission->reward_revoked_at->format('M j, Y g:ia') }}</p>
                            <p class="text-sm mt-1">{{ $selectedSubmission->reward_revocation_reason }}</p>
                        </div>
                    </div>
                @else
                    <div x-data="{ revoking: false }">
                        <div x-show="!revoking" class="flex justify-end">
                            <button type="button" @click="revoking = true"
                                class="inline-flex items-center gap-1.5 bg-trenakt-danger/10 text-trenakt-danger text-sm font-semibold rounded-md px-4 py-2.5 hover:opacity-80 transition">
                                <x-icon name="alert-triangle" class="w-4 h-4" />
                                Revoke reward
                            </button>
                        </div>

                        <div x-show="revoking" x-cloak>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Why is this reward being revoked? This is shown to the participant.</p>
                            <textarea wire:model="revokeReason" rows="3" placeholder="e.g. Evidence was found to be fabricated after payment"
                                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-danger mb-2"></textarea>
                            @error('revokeReason') <p class="text-xs text-trenakt-danger mb-3">{{ $message }}</p> @enderror

                            @if ($participantBalance !== null && $participantBalance < ($selectedSubmission->campaign->rate_per_participant ?? 0))
                                <p class="text-xs text-trenakt-warning mb-3">
                                    Heads up: this participant's current balance (₦{{ number_format($participantBalance, 2) }}) is already below the ₦{{ number_format($selectedSubmission->campaign->rate_per_participant ?? 0, 2) }} reward, so some or all of it may already be withdrawn. Revoking still removes it from their earnings - it can take their balance negative rather than being pulled back from a bank account.
                                </p>
                            @endif

                            <div class="flex justify-end gap-3">
                                <button type="button" @click="revoking = false" wire:loading.attr="disabled" wire:target="revoke" class="text-sm font-medium text-gray-500">Cancel</button>
                                <button type="button" wire:click="revoke" wire:loading.attr="disabled" wire:target="revoke"
                                    class="bg-trenakt-danger text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                                    <span wire:loading.remove wire:target="revoke">Revoke ₦{{ number_format($selectedSubmission->campaign->rate_per_participant ?? 0, 2) }}</span>
                                    <span wire:loading wire:target="revoke">Revoking...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @elseif ($selectedSubmission->status === 'rejected')
                <div class="flex items-start gap-3 bg-trenakt-danger/10 text-trenakt-danger rounded-lg p-3.5">
                    <x-icon name="alert-triangle" class="w-4.5 h-4.5 shrink-0 mt-0.5" />
                    <p class="text-sm">{{ $selectedSubmission->rejection_reason }}</p>
                </div>
            @endif
        @endif
    </x-modal>
</div>
