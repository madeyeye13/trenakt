<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('campaigns.index') }}" wire:navigate class="text-xs font-medium text-trenakt-primary hover:underline">Campaigns</a>
            <h1 class="text-2xl font-bold mt-1">{{ $campaign->title }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $campaign->category->name }} &middot; Performance</p>
        </div>
        <a href="{{ route('campaigns.submissions', $campaign) }}" wire:navigate class="inline-flex items-center justify-center border border-gray-200 dark:border-white/10 text-sm font-medium rounded-md px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
            View submissions
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
            <p class="text-xs text-gray-400 dark:text-white/40">Estimated reach</p>
            <p class="text-xl font-bold mt-1">{{ number_format($summary['engagement']['reach_estimate']) }}</p>
            <p class="text-[11px] text-gray-400 dark:text-white/30 mt-0.5">Participants matching targeting</p>
        </div>
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
            <p class="text-xs text-gray-400 dark:text-white/40">Views</p>
            <p class="text-xl font-bold mt-1">{{ number_format($summary['engagement']['views']) }}</p>
            <p class="text-[11px] text-gray-400 dark:text-white/30 mt-0.5">Saw it on Discover</p>
        </div>
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
            <p class="text-xs text-gray-400 dark:text-white/40">Opened details</p>
            <p class="text-xl font-bold mt-1">{{ number_format($summary['engagement']['detail_opens']) }}</p>
            <p class="text-[11px] text-gray-400 dark:text-white/30 mt-0.5">Looked at the full task</p>
        </div>
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
            <p class="text-xs text-gray-400 dark:text-white/40">Submissions</p>
            <p class="text-xl font-bold mt-1">{{ number_format($summary['submissions']['total']) }}</p>
            <p class="text-[11px] text-gray-400 dark:text-white/30 mt-0.5">{{ $summary['submissions']['approved'] }} approved &middot; {{ $summary['submissions']['rejected'] }} rejected</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-6 min-w-0">
        <div class="lg:col-span-2 min-w-0 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
            <p class="text-sm font-semibold mb-4">Funnel</p>
            <x-chart type="bar" wire:key="funnel-{{ $campaign->id }}"
                :labels="['Reach', 'Views', 'Opened', 'Submitted', 'Approved']"
                :datasets="[[
                    'label' => 'Participants',
                    'data' => [
                        $summary['engagement']['reach_estimate'],
                        $summary['engagement']['views'],
                        $summary['engagement']['detail_opens'],
                        $summary['submissions']['total'],
                        $summary['submissions']['approved'],
                    ],
                ]]"
                :height="220" />
        </div>

        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 space-y-4">
            <p class="text-sm font-semibold">Completion</p>
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-500 dark:text-gray-400">Fill rate</span>
                    <span class="font-semibold">{{ $summary['completion']['fill_rate'] }}%</span>
                </div>
                <div class="h-2 bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-trenakt-primary rounded-full" style="width: {{ min($summary['completion']['fill_rate'], 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-500 dark:text-gray-400">Approval rate</span>
                    <span class="font-semibold">{{ $summary['completion']['approval_rate'] }}%</span>
                </div>
                <div class="h-2 bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                    <div class="h-full bg-trenakt-accent rounded-full" style="width: {{ min($summary['completion']['approval_rate'], 100) }}%"></div>
                </div>
            </div>
            <div class="pt-2 border-t border-gray-100 dark:border-white/10">
                <p class="text-xs text-gray-400 dark:text-white/40">Average review time</p>
                <p class="text-sm font-medium mt-0.5">
                    {{ $summary['completion']['avg_review_hours'] !== null ? number_format($summary['completion']['avg_review_hours'], 1) . ' hours' : 'No reviews yet' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 min-w-0">
        <div class="lg:col-span-2 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
            <p class="text-sm font-semibold mb-4">Spending</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-400 dark:text-white/40">Total budget</p>
                    <p class="text-base font-semibold mt-0.5"><x-currency :amount="$summary['financials']['total_budget']" /></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-white/40">Spent</p>
                    <p class="text-base font-semibold mt-0.5"><x-currency :amount="$summary['financials']['spent']" /></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-white/40">Platform fee</p>
                    <p class="text-base font-semibold mt-0.5"><x-currency :amount="$summary['financials']['platform_fee']" /></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-white/40">Paid to participants</p>
                    <p class="text-base font-semibold mt-0.5 text-trenakt-primary"><x-currency :amount="$summary['financials']['payout_to_date']" /></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-white/40">Remaining reward pool</p>
                    <p class="text-base font-semibold mt-0.5"><x-currency :amount="$summary['financials']['payout_remaining']" /></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 dark:text-white/40">Cost per approval</p>
                    <p class="text-base font-semibold mt-0.5">
                        {{ $summary['financials']['cost_per_approval'] !== null ? '' : 'N/A' }}
                        @if ($summary['financials']['cost_per_approval'] !== null)
                            <x-currency :amount="$summary['financials']['cost_per_approval']" />
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="min-w-0 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
            <p class="text-sm font-semibold mb-4">Participants by country</p>
            @if ($summary['participantsByCountry']->isEmpty())
                <x-empty-state icon="user" title="No submissions yet" description="Country breakdown will show up once participants start submitting." />
            @else
                <x-chart type="bar" wire:key="country-{{ $campaign->id }}"
                    :labels="$summary['participantsByCountry']->keys()->all()"
                    :datasets="[['label' => 'Participants', 'data' => $summary['participantsByCountry']->values()->all()]]"
                    :height="180" />
            @endif
        </div>
    </div>
</div>
