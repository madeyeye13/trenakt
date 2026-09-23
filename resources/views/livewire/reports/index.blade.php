<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Promoting</p>
            <h1 class="text-2xl font-bold">Reports</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Spend and performance across every campaign.</p>
        </div>
        <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
            class="inline-flex items-center justify-center border border-gray-200 dark:border-white/10 text-sm font-medium rounded-md px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-white/5 transition disabled:opacity-60">
            <span wire:loading.remove wire:target="exportCsv">Export CSV</span>
            <span wire:loading wire:target="exportCsv">Preparing...</span>
        </button>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-6 min-w-0">
        <div class="lg:col-span-2 min-w-0 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
            <p class="text-sm font-semibold mb-4">Spend over time</p>
            <x-chart type="line" wire:key="spend-over-time"
                :labels="$spendOverTime['labels']"
                :datasets="[['label' => 'Spend', 'data' => $spendOverTime['data']]]"
                :currency="true"
                :height="240" />
        </div>

        <div class="min-w-0 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
            <p class="text-sm font-semibold mb-4">Spend by category</p>
            @if ($spendByCategory->isEmpty())
                <x-empty-state icon="wallet" title="No spend yet" description="Once a campaign goes live, its spend will show up here." />
            @else
                <x-chart type="bar" wire:key="spend-by-category"
                    :labels="$spendByCategory->keys()->all()"
                    :datasets="[['label' => 'Spend', 'data' => $spendByCategory->values()->all()]]"
                    :currency="true"
                    :height="240" />
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
            <p class="text-sm font-semibold">Campaigns</p>
        </div>

        @if ($campaigns->isEmpty())
            <x-empty-state icon="briefcase" title="No campaigns yet" description="Create a campaign to start seeing performance data here." />
        @else
            <div class="overflow-x-auto scrollbar-brand">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 dark:text-white/40 border-b border-gray-100 dark:border-white/10">
                            <th class="px-5 py-3 font-medium">Campaign</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Submissions</th>
                            <th class="px-5 py-3 font-medium text-right">Approved</th>
                            <th class="px-5 py-3 font-medium text-right">Fill rate</th>
                            <th class="px-5 py-3 font-medium text-right">Spent</th>
                            <th class="px-5 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($campaigns as $row)
                            @php
                                $statusClass = match ($row['status']) {
                                    'approved' => 'bg-trenakt-success-light text-trenakt-success',
                                    'rejected' => 'bg-trenakt-danger/10 text-trenakt-danger',
                                    'completed' => 'bg-trenakt-primary/10 text-trenakt-primary',
                                    default => 'bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400',
                                };
                            @endphp
                            <tr>
                                <td class="px-5 py-3.5">
                                    <p class="font-medium truncate max-w-[220px]">{{ $row['title'] }}</p>
                                    <p class="text-xs text-gray-400 dark:text-white/40">{{ $row['category'] }}</p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $row['status'])) }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right">{{ $row['submissions'] }}</td>
                                <td class="px-5 py-3.5 text-right">{{ $row['approved'] }}</td>
                                <td class="px-5 py-3.5 text-right">{{ $row['fill_rate'] }}%</td>
                                <td class="px-5 py-3.5 text-right"><x-currency :amount="$row['spent']" /></td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('campaigns.performance', $row['id']) }}" wire:navigate class="text-xs font-medium text-trenakt-primary hover:underline">
                                        Performance
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
