<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Earning</p>
            <h1 class="text-2xl font-bold">Withdrawals</h1>
            <p class="text-sm text-gray-500 dark:text-white/50 mt-1">Requests are paid out automatically on the configured payout day.</p>
        </div>
        @if ($counts['pending'] > 0)
            <button type="button" @click="$dispatch('open-modal', { name: 'run-payout' })"
                class="inline-flex items-center justify-center bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition">
                Run payout now
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @foreach ([
            'pending' => 'Pending',
            'processing' => 'Processing',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'all' => 'All',
        ] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')"
                class="text-left bg-white dark:bg-trenakt-surface-dark border rounded-lg p-4 transition {{ $status === $key ? 'border-trenakt-accent ring-1 ring-trenakt-accent' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20' }}">
                <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ $label }}</p>
                <p class="text-xl font-bold mt-1">{{ $counts[$key] }}</p>
            </button>
        @endforeach
    </div>

    <div wire:loading.remove wire:target="status,page" class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
        @if ($withdrawals->isEmpty())
            <x-empty-state icon="bank" title="No withdrawals" description="No withdrawal requests match this filter." />
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($withdrawals as $withdrawal)
                    @php
                        $statusClass = match ($withdrawal->status) {
                            'paid' => 'bg-trenakt-success-light text-trenakt-success',
                            'failed' => 'bg-trenakt-danger/10 text-trenakt-danger',
                            'processing' => 'bg-trenakt-primary/10 text-trenakt-primary',
                            default => 'bg-trenakt-warning-light text-trenakt-warning',
                        };
                    @endphp
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium truncate">{{ $withdrawal->user->name ?? 'Deleted user' }}</p>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1">
                                Requested {{ $withdrawal->requested_at->format('M j, Y g:ia') }}
                                @if ($withdrawal->status === 'failed' && $withdrawal->failure_reason)
                                    &middot; {{ $withdrawal->failure_reason }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="font-semibold">₦{{ number_format($withdrawal->amount, 2) }}</span>
                            <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ ucfirst($withdrawal->status) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-5">{{ $withdrawals->links() }}</div>
        @endif
    </div>

    <div wire:loading.flex wire:target="status,page" class="flex flex-col gap-3">
        @for ($i = 0; $i < 5; $i++)
            <x-skeleton-card />
        @endfor
    </div>

    <x-modal name="run-payout">
        <div class="w-11 h-11 rounded-full bg-trenakt-accent/10 text-trenakt-accent flex items-center justify-center mb-4">
            <x-icon name="bank" class="w-5.5 h-5.5" />
        </div>
        <h3 class="text-lg font-semibold mb-2">Run payout now?</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            This sends every pending withdrawal to the payment gateway immediately, regardless of the configured payout day. This cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500" wire:loading.attr="disabled" wire:target="runPayout">Cancel</button>
            <button type="button" wire:click="runPayout" wire:loading.attr="disabled" wire:target="runPayout"
                class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                <span wire:loading.remove wire:target="runPayout">Run payout</span>
                <span wire:loading wire:target="runPayout">Running...</span>
            </button>
        </div>
    </x-modal>
</div>
