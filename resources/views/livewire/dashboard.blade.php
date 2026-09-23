<div>
    @if ($mode === 'business')
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Promoting overview</p>
                <h1 class="text-2xl font-bold">Dashboard</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('wallet.index') }}" wire:navigate class="inline-flex items-center justify-center border border-gray-200 dark:border-white/10 text-sm font-medium rounded-md px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    Fund wallet
                </a>
                <a href="{{ route('campaigns.create') }}" wire:navigate class="inline-flex items-center justify-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition">
                    Create campaign
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Wallet balance</p>
                <p class="text-xl font-bold text-trenakt-primary">₦{{ number_format($availableBalance, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Live campaigns</p>
                <p class="text-xl font-bold">{{ $campaignCounts['approved'] }}</p>
            </div>
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Pending review</p>
                <p class="text-xl font-bold">{{ $campaignCounts['pending_review'] }}</p>
            </div>
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Total spent</p>
                <p class="text-xl font-bold">₦{{ number_format($totalSpent, 2) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <p class="text-sm font-semibold">Recent campaigns</p>
                <a href="{{ route('campaigns.index') }}" wire:navigate class="text-xs font-medium text-trenakt-primary hover:underline">View all</a>
            </div>

            @if ($recentCampaigns->isEmpty())
                <x-empty-state icon="briefcase" title="No campaigns yet"
                    description="Create your first campaign to start reaching participants.">
                    <a href="{{ route('campaigns.create') }}" wire:navigate class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2">Create campaign</a>
                </x-empty-state>
            @else
                <div class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($recentCampaigns as $campaign)
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
                        <a href="{{ route('campaigns.submissions', $campaign) }}" wire:navigate class="flex items-center justify-between gap-4 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $campaign->title }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">{{ $campaign->category->name }} &middot; {{ $campaign->submissions_count }}/{{ $campaign->target_participants }} submissions</p>
                            </div>
                            <span class="shrink-0 text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ $statusLabel }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Earning overview</p>
                <h1 class="text-2xl font-bold">Dashboard</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('earnings.index') }}" wire:navigate class="inline-flex items-center justify-center border border-gray-200 dark:border-white/10 text-sm font-medium rounded-md px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    View earnings
                </a>
                <a href="{{ route('tasks.discover') }}" wire:navigate class="inline-flex items-center justify-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition">
                    Find tasks
                </a>
            </div>
        </div>

        @if (! $profileComplete)
            <div class="flex items-start gap-3 bg-trenakt-warning-light text-trenakt-warning rounded-lg p-4 mb-6">
                <x-icon name="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-semibold">Complete your profile to see available tasks</p>
                    <p class="text-sm mt-0.5">
                        <a href="{{ route('profile.edit') }}" wire:navigate class="underline font-medium">Finish your profile</a> to unlock the task marketplace.
                    </p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Available balance</p>
                <p class="text-xl font-bold text-trenakt-primary"><x-currency :amount="$availableBalance" /></p>
            </div>
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Available tasks</p>
                <p class="text-xl font-bold">{{ $availableTasksCount }}</p>
            </div>
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Waiting for review</p>
                <p class="text-xl font-bold">{{ $submissionCounts['submitted'] }}</p>
            </div>
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Tasks done</p>
                <p class="text-xl font-bold">{{ $submissionCounts['approved'] }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <p class="text-sm font-semibold">Recent tasks</p>
                <a href="{{ route('tasks.index') }}" wire:navigate class="text-xs font-medium text-trenakt-primary hover:underline">View all</a>
            </div>

            @if ($recentSubmissions->isEmpty())
                <x-empty-state icon="briefcase" title="No tasks yet"
                    description="Once you submit a task, it'll show up here with its review status.">
                    <a href="{{ route('tasks.discover') }}" wire:navigate class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2">Find tasks</a>
                </x-empty-state>
            @else
                <div class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($recentSubmissions as $submission)
                        @php
                            $statusLabel = match ($submission->status) {
                                'submitted' => 'Waiting for review',
                                'approved' => 'Done',
                                'rejected' => 'Rejected',
                                default => ucfirst($submission->status),
                            };
                            $statusClass = match ($submission->status) {
                                'approved' => 'bg-trenakt-success-light text-trenakt-success',
                                'rejected' => 'bg-trenakt-danger/10 text-trenakt-danger',
                                default => 'bg-trenakt-warning-light text-trenakt-warning',
                            };
                        @endphp
                        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $submission->campaign->title ?? 'Deleted campaign' }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">{{ $submission->campaign->category->name ?? 'Uncategorized' }} &middot; {{ $submission->submitted_at->format('M j, Y') }}</p>
                            </div>
                            <span class="shrink-0 text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
