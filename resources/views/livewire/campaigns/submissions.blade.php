<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('campaigns.index') }}" wire:navigate class="text-xs font-medium text-trenakt-primary hover:underline">Campaigns</a>
            <h1 class="text-2xl font-bold mt-1">{{ $campaign->title }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Participant submissions</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('campaigns.performance', $campaign) }}" wire:navigate class="text-xs font-medium text-trenakt-primary hover:underline">
                View performance
            </a>
            <span class="text-xs text-gray-400 dark:text-white/40">{{ $submissions->total() }} total</span>
        </div>
    </div>

    <div wire:loading.remove wire:target="page" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
            <p class="text-xs text-gray-400 dark:text-white/40">Target participants</p>
            <p class="text-xl font-bold mt-1">{{ $campaign->target_participants }}</p>
        </div>
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4">
            <p class="text-xs text-gray-400 dark:text-white/40">Submitted</p>
            <p class="text-xl font-bold text-trenakt-primary mt-1">{{ $submissions->total() }}</p>
        </div>
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4 col-span-2 sm:col-span-1">
            <p class="text-xs text-gray-400 dark:text-white/40">Rate per participant</p>
            <p class="text-lg sm:text-xl font-bold mt-1">₦{{ number_format($campaign->rate_per_participant, 2) }}</p>
        </div>
    </div>

    <div wire:loading.remove wire:target="page,status" class="flex flex-wrap gap-2 mb-4">
        @foreach ([
            'all' => 'All',
            'submitted' => 'Pending review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ] as $key => $label)
            <button type="button" wire:click="$set('status', '{{ $key }}')"
                class="text-xs font-medium border rounded-full px-3 py-1.5 transition
                    {{ $status === $key ? 'border-trenakt-primary bg-trenakt-primary/10 text-trenakt-primary' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400' }}">
                {{ $label }} ({{ $counts[$key] }})
            </button>
        @endforeach
    </div>

    <div wire:loading.remove wire:target="page,status" class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
        @if ($submissions->isEmpty())
            <x-empty-state icon="user" title="No submissions yet"
                description="When participants complete this campaign, their answers will appear here for review." />
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($submissions as $submission)
                    @php
                        $statusClass = match ($submission->status) {
                            'approved' => 'bg-trenakt-success-light text-trenakt-success',
                            'rejected' => 'bg-trenakt-danger/10 text-trenakt-danger',
                            default => 'bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400',
                        };
                    @endphp
                    <button type="button" wire:click="showSubmission({{ $submission->id }})" class="w-full text-left px-5 py-4 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $submission->participant->name }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Submitted {{ $submission->submitted_at->format('M j, Y g:ia') }}</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ ucfirst($submission->status) }}</span>
                                <x-icon name="eye" class="w-4 h-4 text-gray-400" />
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>
            <div class="p-5">{{ $submissions->links() }}</div>
        @endif
    </div>

    <div wire:loading.flex wire:target="page" class="flex flex-col gap-6" style="row-gap: 1.5rem;">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3" style="gap: 0.75rem;">
            @for ($i = 0; $i < 3; $i++)
                <div class="w-full bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-4 space-y-2 animate-pulse">
                    <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-2/3"></div>
                    <div class="h-6 bg-gray-200 dark:bg-white/10 rounded w-1/3"></div>
                </div>
            @endfor
        </div>

        <div class="w-full bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden animate-pulse">
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @for ($i = 0; $i < 6; $i++)
                    <div class="px-5 py-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-2/5"></div>
                                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/3"></div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="h-5 bg-gray-200 dark:bg-white/10 rounded-full w-20"></div>
                                <div class="h-4 w-4 bg-gray-200 dark:bg-white/10 rounded"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <x-modal name="submission-details" maxWidth="lg">
        @if ($selectedSubmission)
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40">Submission</p>
                    <h2 class="text-lg font-semibold mt-1">{{ $selectedSubmission->participant->name }}</h2>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1">{{ $selectedSubmission->participant->email }}</p>
                </div>
                <button type="button" @click="$dispatch('close-modal')" class="text-gray-400 hover:text-trenakt-dark dark:hover:text-white" title="Close">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1 scrollbar-brand">
                @foreach ($platformLinks as $platform => $label)
                    @php $answer = data_get($selectedSubmission->answers, 'platform_links.' . $platform); @endphp
                    <div class="border-b border-gray-100 dark:border-white/10 pb-4 last:border-0">
                        <p class="text-xs text-gray-400 dark:text-white/40 mb-1">{{ $label }} post link</p>
                        @if ($answer)
                            <a href="{{ $answer }}" target="_blank" rel="noopener" class="text-sm text-trenakt-primary hover:underline break-all">{{ $answer }}</a>
                        @else
                            <p class="text-sm whitespace-pre-wrap">No answer provided</p>
                        @endif
                    </div>
                @endforeach

                @foreach ($participantFields as $field)
                    @php $answer = data_get($selectedSubmission->answers, (string) $field->id); @endphp
                    <div class="border-b border-gray-100 dark:border-white/10 pb-4 last:border-0">
                        <p class="text-xs text-gray-400 dark:text-white/40 mb-1">{{ $field->label }}</p>
                        @if ($field->type === 'url' && $answer)
                            <a href="{{ $answer }}" target="_blank" rel="noopener" class="text-sm text-trenakt-primary hover:underline break-all">{{ $answer }}</a>
                        @else
                            <p class="text-sm whitespace-pre-wrap">{{ $answer ?: 'No answer provided' }}</p>
                        @endif
                    </div>
                @endforeach

                @foreach ($customFields as $field)
                    @php $answer = data_get($selectedSubmission->answers, $field->field_key); @endphp
                    <div class="border-b border-gray-100 dark:border-white/10 pb-4 last:border-0">
                        <p class="text-xs text-gray-400 dark:text-white/40 mb-1">{{ $field->label }}</p>
                        @if ($field->type === 'url' && $answer)
                            <a href="{{ $answer }}" target="_blank" rel="noopener" class="text-sm text-trenakt-primary hover:underline break-all">{{ $answer }}</a>
                        @else
                            <p class="text-sm whitespace-pre-wrap">{{ $answer ?: 'No answer provided' }}</p>
                        @endif
                    </div>
                @endforeach

                @if ($participantFields->isEmpty() && $customFields->isEmpty() && $platformLinks->isEmpty())
                    <x-empty-state icon="info-circle" title="No participant fields" description="This campaign has no participant requirements." />
                @endif
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Close</button>
            </div>
        @endif
    </x-modal>
</div>
