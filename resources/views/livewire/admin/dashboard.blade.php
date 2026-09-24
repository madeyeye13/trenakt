<div>
    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Platform overview</p>
    <h1 class="text-2xl font-bold mb-6">Dashboard</h1>

    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-3">Needs attention</p>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('admin.campaigns.index') }}" wire:navigate
            class="flex items-center gap-4 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 hover:border-trenakt-accent/40 transition">
            <div class="w-11 h-11 shrink-0 rounded-full flex items-center justify-center {{ $pendingCampaigns > 0 ? 'bg-trenakt-warning-light text-trenakt-warning' : 'bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-white/30' }}">
                <x-icon name="briefcase" class="w-5 h-5" />
            </div>
            <div>
                <p class="text-xl font-bold">{{ $pendingCampaigns }}</p>
                <p class="text-xs text-gray-400 dark:text-white/40">Campaigns awaiting review</p>
            </div>
        </a>
        <a href="{{ route('admin.submissions.index') }}" wire:navigate
            class="flex items-center gap-4 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 hover:border-trenakt-accent/40 transition">
            <div class="w-11 h-11 shrink-0 rounded-full flex items-center justify-center {{ $pendingSubmissions > 0 ? 'bg-trenakt-warning-light text-trenakt-warning' : 'bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-white/30' }}">
                <x-icon name="clock" class="w-5 h-5" />
            </div>
            <div>
                <p class="text-xl font-bold">{{ $pendingSubmissions }}</p>
                <p class="text-xs text-gray-400 dark:text-white/40">Task submissions awaiting review</p>
            </div>
        </a>
        <a href="{{ route('admin.withdrawals.index') }}" wire:navigate
            class="flex items-center gap-4 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 hover:border-trenakt-accent/40 transition">
            <div class="w-11 h-11 shrink-0 rounded-full flex items-center justify-center {{ $pendingWithdrawalsCount > 0 ? 'bg-trenakt-warning-light text-trenakt-warning' : 'bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-white/30' }}">
                <x-icon name="bank" class="w-5 h-5" />
            </div>
            <div>
                <p class="text-xl font-bold">{{ $pendingWithdrawalsCount }}</p>
                <p class="text-xs text-gray-400 dark:text-white/40">Withdrawals pending &middot; ₦{{ number_format($pendingWithdrawalsAmount, 2) }}</p>
            </div>
        </a>
    </div>

    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-3">Platform totals</p>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
            <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Businesses</p>
            <p class="text-xl font-bold">{{ number_format($totalBusinesses) }}</p>
        </div>
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
            <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Participants</p>
            <p class="text-xl font-bold">{{ number_format($totalParticipants) }}</p>
        </div>
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
            <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Total campaign spend</p>
            <p class="text-lg sm:text-xl font-bold text-trenakt-accent">₦{{ number_format($totalSpent, 2) }}</p>
        </div>
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
            <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Total rewards paid</p>
            <p class="text-lg sm:text-xl font-bold text-trenakt-accent">₦{{ number_format($totalRewardsPaid, 2) }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40">Income vs withdrawals</p>
        <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
            class="inline-flex items-center justify-center border border-gray-200 dark:border-white/10 text-xs font-medium rounded-md px-3 py-2 hover:bg-gray-50 dark:hover:bg-white/5 transition disabled:opacity-60">
            <span wire:loading.remove wire:target="exportCsv">Export CSV</span>
            <span wire:loading wire:target="exportCsv">Preparing...</span>
        </button>
    </div>
    <div class="min-w-0 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 mb-8">
        <x-chart type="line" wire:key="income-vs-withdrawals"
            :labels="$incomeVsWithdrawals['labels']"
            :datasets="[
                ['label' => 'Income', 'data' => $incomeVsWithdrawals['income']],
                ['label' => 'Withdrawals', 'data' => $incomeVsWithdrawals['withdrawals']],
            ]"
            :currency="true"
            :height="260" />
    </div>

    <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
            <p class="text-sm font-semibold">Recent activity</p>
        </div>

        @if ($recentActivity->isEmpty())
            <x-empty-state icon="check-circle" title="All caught up" description="Nothing is waiting on you right now." />
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($recentActivity as $item)
                    <a href="{{ $item['url'] }}" wire:navigate class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                        <div class="w-9 h-9 shrink-0 rounded-full bg-trenakt-accent/10 text-trenakt-accent flex items-center justify-center">
                            <x-icon name="{{ $item['icon'] }}" class="w-4.5 h-4.5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm truncate">{{ $item['label'] }}</p>
                        </div>
                        <span class="shrink-0 text-xs text-gray-400 dark:text-white/40">{{ $item['at']?->diffForHumans() }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
