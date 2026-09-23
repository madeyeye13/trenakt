<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Management</p>
            <h1 class="text-2xl font-bold">Campaign review</h1>
            <p class="text-sm text-gray-500 dark:text-white/50 mt-1">Approve campaigns to make them live, or reject them to release the held funds.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach ([
            'pending_review' => 'Pending review',
            'approved' => 'Live',
            'rejected' => 'Rejected',
            'all' => 'All campaigns',
        ] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')"
                class="text-left bg-white dark:bg-trenakt-surface-dark border rounded-lg p-4 transition {{ $status === $key ? 'border-trenakt-accent ring-1 ring-trenakt-accent' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20' }}">
                <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ $label }}</p>
                <p class="text-xl font-bold mt-1">{{ $counts[$key] }}</p>
            </button>
        @endforeach
    </div>

    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1">
            <x-icon name="briefcase" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by campaign or business name"
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-accent">
        </div>
    </div>

    <div wire:loading.remove wire:target="status,search,page">
        @if ($campaigns->isEmpty())
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg">
                <x-empty-state icon="briefcase" title="Nothing here"
                    description="No campaigns match this filter right now." />
            </div>
        @else
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
                <div class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($campaigns as $campaign)
                        @php
                            $statusLabel = match ($campaign->status) {
                                'pending_review' => 'Pending review',
                                'approved' => 'Live',
                                default => ucfirst($campaign->status),
                            };
                            $statusClass = match ($campaign->status) {
                                'approved' => 'bg-trenakt-success-light text-trenakt-success',
                                'rejected' => 'bg-trenakt-danger/10 text-trenakt-danger',
                                'completed' => 'bg-trenakt-primary/10 text-trenakt-primary',
                                default => 'bg-trenakt-warning-light text-trenakt-warning',
                            };
                        @endphp
                        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ $statusLabel }}</span>
                                    <span class="text-xs text-gray-400 dark:text-white/40">{{ $campaign->category->name }}</span>
                                </div>
                                <a href="{{ route('admin.campaigns.show', $campaign) }}" wire:navigate class="font-semibold hover:text-trenakt-accent transition truncate block">
                                    {{ $campaign->title }}
                                </a>
                                <p class="text-xs text-gray-400 dark:text-white/40 mt-1">
                                    {{ $campaign->business->name }} &middot;
                                    @if ($campaign->submitted_at)
                                        submitted {{ $campaign->submitted_at->diffForHumans() }}
                                    @else
                                        not yet submitted
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center justify-between sm:justify-end gap-x-5 gap-y-3 w-full sm:w-auto">
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-white/40">Budget</p>
                                    <p class="text-sm font-medium mt-0.5">₦{{ number_format($campaign->total_budget, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-white/40">Participants</p>
                                    <p class="text-sm font-medium mt-0.5">{{ $campaign->target_participants }}</p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ route('admin.campaigns.show', $campaign) }}" wire:navigate
                                        class="text-gray-400 hover:text-trenakt-dark dark:hover:text-white transition" title="View details">
                                        <x-icon name="eye" class="w-4.5 h-4.5" />
                                    </a>

                                    @if ($campaign->status === 'pending_review')
                                        <button type="button" @click="$dispatch('open-modal', { name: 'approve-campaign', id: {{ $campaign->id }}, title: @js($campaign->title), amount: @js(number_format($campaign->total_budget, 2)) })"
                                            class="inline-flex items-center gap-1 bg-trenakt-success-light text-trenakt-success text-xs font-semibold rounded-md px-2.5 py-1.5 hover:opacity-80 transition">
                                            <x-icon name="check" class="w-3.5 h-3.5" />
                                            Approve
                                        </button>
                                        <button type="button" wire:click="confirmReject({{ $campaign->id }})"
                                            class="inline-flex items-center gap-1 bg-trenakt-danger/10 text-trenakt-danger text-xs font-semibold rounded-md px-2.5 py-1.5 hover:opacity-80 transition">
                                            <x-icon name="x" class="w-3.5 h-3.5" />
                                            Reject
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6">{{ $campaigns->links() }}</div>
        @endif
    </div>

    <div wire:loading.flex wire:target="status,search,page" class="flex-col gap-0 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden divide-y divide-gray-100 dark:divide-white/10">
        @for ($i = 0; $i < 5; $i++)
            <div class="px-5 py-4 flex items-center justify-between gap-4 animate-pulse">
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/4"></div>
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-1/2"></div>
                </div>
                <div class="h-8 bg-gray-200 dark:bg-white/10 rounded w-32 shrink-0"></div>
            </div>
        @endfor
    </div>

    {{-- Approve confirmation --}}
    <x-modal name="approve-campaign">
        <div x-data="{ approveId: null, approveTitle: '', approveAmount: '' }"
            x-on:open-modal.window="if ($event.detail.name === 'approve-campaign') { approveId = $event.detail.id; approveTitle = $event.detail.title; approveAmount = $event.detail.amount; }">
            <div class="w-11 h-11 rounded-full bg-trenakt-success-light text-trenakt-success flex items-center justify-center mb-4">
                <x-icon name="check-circle" class="w-5.5 h-5.5" />
            </div>
            <h3 class="text-lg font-semibold mb-2">Approve <span x-text="approveTitle"></span>?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                ₦<span x-text="approveAmount"></span> moves from reserved to spent and the campaign goes live immediately. This cannot be undone.
            </p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500" wire:loading.attr="disabled" wire:target="approve">Cancel</button>
                <button type="button" @click="$wire.approve(approveId)" wire:loading.attr="disabled" wire:target="approve"
                    class="bg-trenakt-success text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="approve">Approve</span>
                    <span wire:loading wire:target="approve">Approving...</span>
                </button>
            </div>
        </div>
    </x-modal>

    {{-- Reject with reason --}}
    <x-modal name="reject-campaign">
        <div class="w-11 h-11 rounded-full bg-trenakt-danger/10 text-trenakt-danger flex items-center justify-center mb-4">
            <x-icon name="x-circle" class="w-5.5 h-5.5" />
        </div>
        <h3 class="text-lg font-semibold mb-2">Reject {{ $rejectingTitle }}?</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            The full held amount is returned to the business's wallet. Let them know why so they can fix it and resubmit.
        </p>
        <textarea wire:model="rejectReason" rows="3" placeholder="Reason for rejection"
            class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-danger"></textarea>
        @error('rejectReason') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
        <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500" wire:loading.attr="disabled" wire:target="reject">Cancel</button>
            <button type="button" wire:click="reject" wire:loading.attr="disabled" wire:target="reject"
                class="bg-trenakt-danger text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                <span wire:loading.remove wire:target="reject">Reject campaign</span>
                <span wire:loading wire:target="reject">Rejecting...</span>
            </button>
        </div>
    </x-modal>
</div>
