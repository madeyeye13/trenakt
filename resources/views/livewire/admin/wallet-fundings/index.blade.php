<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Earning</p>
            <h1 class="text-2xl font-bold">Wallet fundings</h1>
            <p class="text-sm text-gray-500 dark:text-white/50 mt-1">Every time a business adds money to their wallet to fund campaigns.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach ([
            'all' => 'All',
            'pending' => 'Pending',
            'successful' => 'Successful',
            'failed' => 'Failed',
        ] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')"
                class="text-left bg-white dark:bg-trenakt-surface-dark border rounded-lg p-4 transition {{ $status === $key ? 'border-trenakt-accent ring-1 ring-trenakt-accent' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20' }}">
                <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ $label }}</p>
                <p class="text-xl font-bold mt-1">{{ $counts[$key] }}</p>
            </button>
        @endforeach
    </div>

    <div wire:loading.remove wire:target="status,page" class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
        @if ($fundings->isEmpty())
            <x-empty-state icon="wallet" title="No wallet fundings" description="No wallet fundings match this filter." />
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($fundings as $funding)
                    @php
                        $statusClass = match ($funding->status) {
                            'successful' => 'bg-trenakt-success-light text-trenakt-success',
                            'failed' => 'bg-trenakt-danger/10 text-trenakt-danger',
                            default => 'bg-trenakt-warning-light text-trenakt-warning',
                        };
                    @endphp
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium truncate">{{ $funding->user->name ?? 'Deleted user' }}</p>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1">
                                {{ $funding->created_at->format('M j, Y g:ia') }}
                                &middot; {{ ucfirst($funding->gateway) }}
                                &middot; {{ $funding->reference }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="font-semibold">₦{{ number_format($funding->amount, 2) }}</span>
                            <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ ucfirst($funding->status) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="p-5">{{ $fundings->links() }}</div>
        @endif
    </div>

    <div wire:loading.flex wire:target="status,page" class="flex flex-col gap-3">
        @for ($i = 0; $i < 5; $i++)
            <x-skeleton-card />
        @endfor
    </div>
</div>
