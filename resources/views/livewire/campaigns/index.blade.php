<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Promoting</p>
            <h1 class="text-2xl font-bold">Your campaigns</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Keep an eye on every campaign from review to completion.</p>
        </div>
        <a href="{{ route('campaigns.create') }}" wire:navigate class="inline-flex items-center justify-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition">
            Create campaign
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @foreach ([
            'all' => 'All campaigns',
            'pending_review' => 'Pending review',
            'approved' => 'Live',
            'rejected' => 'Rejected',
            'completed' => 'Completed',
        ] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')"
                class="text-left bg-white dark:bg-trenakt-surface-dark border rounded-lg p-4 transition {{ $status === $key ? 'border-trenakt-primary ring-1 ring-trenakt-primary' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20' }}">
                <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ $label }}</p>
                <p class="text-xl font-bold mt-1">{{ $counts[$key] }}</p>
            </button>
        @endforeach
    </div>

    <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1">
            <x-icon name="briefcase" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search campaigns"
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-primary">
        </div>
        <div class="sm:w-48">
            <x-select model="status" :options="[
                'all' => 'All statuses',
                'pending_review' => 'Pending review',
                'approved' => 'Live',
                'rejected' => 'Rejected',
                'completed' => 'Completed',
            ]" :selected="$status" />
        </div>
    </div>

    <div wire:loading.remove wire:target="status,search">
        @if ($campaigns->isEmpty())
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg">
                <x-empty-state icon="briefcase" title="No campaigns found"
                    description="Create a campaign to start reaching participants, or change your filters to see more." />
            </div>
        @else
            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
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
                            default => 'bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400',
                        };
                    @endphp
                    <article class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 flex flex-col">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">{{ $campaign->category->name }}</p>
                                <h2 class="font-semibold truncate">{{ $campaign->title }}</h2>
                            </div>
                            <span class="shrink-0 text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 border-y border-gray-100 dark:border-white/10 py-4 my-4 text-sm">
                            <div>
                                <p class="text-xs text-gray-400 dark:text-white/40">Budget</p>
                                <p class="font-medium mt-1">₦{{ number_format($campaign->total_budget, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 dark:text-white/40">Submissions</p>
                                <p class="font-medium mt-1">{{ $campaign->submissions_count }} / {{ $campaign->target_participants }}</p>
                            </div>
                        </div>

                        @if ($campaign->status === 'rejected' && $campaign->rejection_reason)
                            <p class="text-xs text-trenakt-danger line-clamp-2 mb-4">{{ $campaign->rejection_reason }}</p>
                        @endif

                        <div class="mt-auto flex items-center justify-between gap-3 pt-1">
                            <span class="text-xs text-gray-400 dark:text-white/40">{{ $campaign->created_at->format('M j, Y') }}</span>
                            <a href="{{ route('campaigns.submissions', $campaign) }}" wire:navigate class="text-xs font-medium text-trenakt-primary hover:underline">
                                View submissions
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">{{ $campaigns->links() }}</div>
        @endif
    </div>

    <div wire:loading.grid wire:target="status,search" class="grid md:grid-cols-2 xl:grid-cols-3 gap-4" style="gap: 1rem;">
        @for ($i = 0; $i < 6; $i++)
            <article class="w-full min-h-[190px] bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 flex flex-col gap-4 animate-pulse">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-2 flex-1">
                        <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/3"></div>
                        <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-3/4"></div>
                    </div>
                    <div class="h-5 bg-gray-200 dark:bg-white/10 rounded-full w-20"></div>
                </div>

                <div class="grid grid-cols-2 gap-3 border-y border-gray-100 dark:border-white/10 py-4">
                    <div class="space-y-2">
                        <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/2"></div>
                        <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-3/4"></div>
                    </div>
                    <div class="space-y-2">
                        <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-2/3"></div>
                        <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-1/2"></div>
                    </div>
                </div>

                <div class="mt-auto flex items-center justify-between gap-3">
                    <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/3"></div>
                    <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-2/5"></div>
                </div>
            </article>
        @endfor
    </div>
</div>
