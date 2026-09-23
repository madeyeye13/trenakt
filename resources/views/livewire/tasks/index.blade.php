<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Earning</p>
            <h1 class="text-2xl font-bold">My tasks</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Track every task you've submitted.</p>
        </div>
        <a href="{{ route('tasks.discover') }}" wire:navigate class="inline-flex items-center justify-center bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition">
            Find tasks
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @foreach ([
            'all' => 'All',
            'submitted' => 'Waiting for review',
            'approved' => 'Done',
            'rejected' => 'Rejected',
        ] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')"
                class="text-left bg-white dark:bg-trenakt-surface-dark border rounded-lg p-4 transition {{ $status === $key ? 'border-trenakt-primary ring-1 ring-trenakt-primary' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20' }}">
                <p class="text-xs text-gray-400 dark:text-white/40 truncate">{{ $label }}</p>
                <p class="text-xl font-bold mt-1">{{ $counts[$key] }}</p>
            </button>
        @endforeach
    </div>

    <div wire:loading.remove wire:target="status,page">
        @if ($submissions->isEmpty())
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg">
                <x-empty-state icon="briefcase" title="No tasks here yet"
                    description="Once you submit a task, it'll show up here with its review status.">
                    <a href="{{ route('tasks.discover') }}" wire:navigate class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2">Find tasks</a>
                </x-empty-state>
            </div>
        @else
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
                <div class="divide-y divide-gray-100 dark:divide-white/10">
                    @foreach ($submissions as $submission)
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
                        <div class="px-5 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-400 dark:text-white/40 mb-0.5">{{ $submission->campaign->category->name ?? 'Uncategorized' }}</p>
                                    <p class="text-sm font-medium truncate">{{ $submission->campaign->title ?? 'Deleted campaign' }}</p>
                                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1">
                                        Submitted {{ $submission->submitted_at->format('M j, Y g:ia') }}
                                        @if ($submission->status === 'approved')
                                            &middot; <span class="text-trenakt-success font-medium">+<x-currency :amount="$submission->campaign->rate_per_participant ?? 0" /></span>
                                        @endif
                                    </p>
                                </div>
                                <span class="shrink-0 text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>

                            @if ($submission->status === 'rejected')
                                <div class="flex items-start gap-2 bg-trenakt-danger/5 text-trenakt-danger rounded-md px-3 py-2.5 mt-3">
                                    <x-icon name="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5" />
                                    <p class="text-xs flex-1">{{ $submission->rejection_reason ?: 'No reason provided.' }}</p>
                                    @if ($submission->campaign)
                                        <a href="{{ route('tasks.discover') }}" wire:navigate class="text-xs font-medium shrink-0 hover:underline">Try again</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="p-5">{{ $submissions->links() }}</div>
            </div>
        @endif
    </div>

    <div wire:loading.flex wire:target="status,page" class="flex flex-col gap-3">
        @for ($i = 0; $i < 4; $i++)
            <x-skeleton-card />
        @endfor
    </div>
</div>
