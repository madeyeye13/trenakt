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
    $genderLabel = match ($campaign->targeting?->gender) {
        'male' => 'Male',
        'female' => 'Female',
        default => 'Any gender',
    };
    $countries = $campaign->targeting?->countries() ?? collect();
    $participantFields = $campaign->category->requirementFields->where('fills_for', 'participant');
@endphp

<div>
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div class="min-w-0">
            <a href="{{ route('admin.campaigns.index') }}" wire:navigate class="text-xs font-medium text-trenakt-accent hover:underline">&larr; Campaign review</a>
            <div class="flex items-center gap-2 mt-2 mb-1">
                <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 {{ $statusClass }}">{{ $statusLabel }}</span>
                <span class="text-xs text-gray-400 dark:text-white/40">{{ $campaign->category->name }}</span>
                @if ($campaign->allow_admin_edit)
                    <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 bg-trenakt-accent/15 text-trenakt-accent">Editable</span>
                @endif
            </div>
            <h1 class="text-2xl font-bold truncate">{{ $campaign->title }}</h1>
            <p class="text-sm text-gray-500 dark:text-white/50 mt-1">
                {{ $campaign->business->name }} &middot; {{ $campaign->business->email }}
            </p>
        </div>

        @if ($campaign->status === 'pending_review')
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="$dispatch('open-modal', { name: 'reject-campaign' })"
                    class="inline-flex items-center gap-1.5 bg-trenakt-danger/10 text-trenakt-danger text-sm font-semibold rounded-md px-4 py-2.5 hover:opacity-80 transition">
                    <x-icon name="x" class="w-4 h-4" />
                    Reject
                </button>
                <button type="button" @click="$dispatch('open-modal', { name: 'approve-campaign' })"
                    class="inline-flex items-center gap-1.5 bg-trenakt-success text-white text-sm font-semibold rounded-md px-4 py-2.5 hover:opacity-90 transition">
                    <x-icon name="check" class="w-4 h-4" />
                    Approve
                </button>
            </div>
        @endif
    </div>

    @if ($campaign->status === 'rejected' && $campaign->rejection_reason)
        <div class="flex items-start gap-3 bg-trenakt-danger/10 text-trenakt-danger rounded-lg p-4 mb-6">
            <x-icon name="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
            <div>
                <p class="text-sm font-semibold">Rejection reason</p>
                <p class="text-sm mt-0.5">{{ $campaign->rejection_reason }}</p>
            </div>
        </div>
    @endif

    @if ($campaign->reviewed_at)
        <p class="text-xs text-gray-400 dark:text-white/40 mb-6">
            Reviewed by {{ $campaign->reviewer?->name ?? 'an admin' }} &middot; {{ $campaign->reviewed_at->format('M j, Y g:ia') }}
        </p>
    @endif

    @if ($campaign->admin_edited_at)
        <p class="text-xs text-gray-400 dark:text-white/40 mb-6">
            Wording edited by {{ $campaign->adminEditor?->name ?? 'an admin' }} &middot; {{ $campaign->admin_edited_at->format('M j, Y g:ia') }}
        </p>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30">Campaign content</p>
                    @if ($campaign->allow_admin_edit && $campaign->status === 'pending_review')
                        @if (! $isEditing)
                            <button type="button" wire:click="startEditing" class="text-xs font-medium text-trenakt-accent hover:underline">Edit</button>
                        @endif
                    @endif
                </div>

                @if ($isEditing)
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Title</label>
                            <input wire:model="editTitle" type="text"
                                class="w-full mt-1 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-accent">
                            @error('editTitle') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Description</label>
                            <textarea wire:model="editDescription" rows="4"
                                class="w-full mt-1 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-accent"></textarea>
                            @error('editDescription') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Steps</label>
                                <button type="button" wire:click="addEditStep" class="text-xs font-medium text-trenakt-accent">+ Add step</button>
                            </div>
                            <div class="space-y-2 mt-2">
                                @foreach ($editSteps as $index => $step)
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-400 dark:text-white/40 w-5 shrink-0">{{ $index + 1 }}.</span>
                                        <input wire:model="editSteps.{{ $index }}" type="text"
                                            class="w-full border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-accent">
                                        <button type="button" wire:click="removeEditStep({{ $index }})" class="text-gray-300 hover:text-trenakt-danger transition shrink-0">
                                            <x-icon name="x" class="w-4 h-4" />
                                        </button>
                                    </div>
                                    @error('editSteps.' . $index) <p class="text-xs text-trenakt-danger ml-7">{{ $message }}</p> @enderror
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-1">
                            <button type="button" wire:click="cancelEditing" wire:loading.attr="disabled" wire:target="saveEdit" class="text-sm font-medium text-gray-500">Cancel</button>
                            <button type="button" wire:click="saveEdit" wire:loading.attr="disabled" wire:target="saveEdit"
                                class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                                <span wire:loading.remove wire:target="saveEdit">Save changes</span>
                                <span wire:loading wire:target="saveEdit">Saving...</span>
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-sm whitespace-pre-wrap">{{ $campaign->description ?: 'No description provided.' }}</p>

                    @if (! empty($campaign->steps))
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10">
                            <p class="text-xs text-gray-400 dark:text-white/40 mb-2">Steps for participants</p>
                            <ol class="space-y-1 text-sm list-decimal list-inside">
                                @foreach ($campaign->steps as $step)
                                    <li>{{ $step }}</li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                @endif
            </div>

            @if ($campaign->task_mode)
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-4">How participants share this</p>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Mode</p>
                            <p class="text-sm font-medium">{{ $campaign->task_mode === 'post_own_content' ? 'Post on their own page' : 'Reshare an existing post' }}</p>
                        </div>
                        @if (! empty($campaign->platforms))
                            <div>
                                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Platforms</p>
                                <p class="text-sm font-medium">{{ collect($campaign->platforms)->map(fn ($p) => \App\Livewire\Campaigns\Create::PLATFORMS[$p] ?? $p)->join(', ') }}</p>
                            </div>
                        @endif
                    </div>

                    @if ($campaign->task_mode === 'post_own_content')
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10 space-y-3">
                            <div>
                                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Caption to post</p>
                                <p class="text-sm whitespace-pre-wrap">{{ $campaign->post_content_text ?: 'No caption provided.' }}</p>
                            </div>
                            @if ($campaign->post_content_media)
                                <div>
                                    <p class="text-xs text-gray-400 dark:text-white/40 mb-1">Media</p>
                                    <a href="{{ asset('storage/' . $campaign->post_content_media) }}" target="_blank" class="text-sm text-trenakt-accent hover:underline">View uploaded file</a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-4">Business-provided requirements</p>

                @if ($campaign->requirementAnswers->isEmpty())
                    <x-empty-state icon="info-circle" title="No requirement fields" description="This category has no business-side requirement fields." />
                @else
                    <div class="space-y-4">
                        @foreach ($campaign->requirementAnswers as $answer)
                            <div class="border-b border-gray-100 dark:border-white/10 pb-4 last:border-0 last:pb-0">
                                <p class="text-xs text-gray-400 dark:text-white/40 mb-1">{{ $answer->field->label ?? 'Field' }}</p>
                                @if (($answer->field->type ?? null) === 'file')
                                    <a href="{{ asset('storage/' . $answer->value) }}" target="_blank" class="text-sm text-trenakt-accent hover:underline">View uploaded file</a>
                                @else
                                    <p class="text-sm whitespace-pre-wrap">{{ $answer->value }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-4">What participants will be asked for</p>

                @if ($participantFields->isEmpty() && $campaign->customFields->isEmpty())
                    <x-empty-state icon="info-circle" title="No participant fields" description="This campaign has no participant requirements set." />
                @else
                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach ($participantFields as $field)
                            <div class="border border-gray-100 dark:border-white/10 rounded-md px-3 py-2.5">
                                <p class="text-sm font-medium">{{ $field->label }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">{{ ucfirst($field->type) }} &middot; {{ $field->is_required ? 'Required' : 'Optional' }}</p>
                            </div>
                        @endforeach
                        @foreach ($campaign->customFields as $field)
                            <div class="border border-gray-100 dark:border-white/10 rounded-md px-3 py-2.5">
                                <p class="text-sm font-medium">{{ $field->label }}</p>
                                <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">{{ ucfirst($field->type) }} &middot; {{ $field->is_required ? 'Required' : 'Optional' }} &middot; Custom</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-4">Budget</p>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Rate per participant</span>
                        <span class="font-medium">₦{{ number_format($campaign->rate_per_participant, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Target participants</span>
                        <span class="font-medium">{{ $campaign->target_participants }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-white/10">
                        <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                        <span class="font-medium">₦{{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Platform fee</span>
                        <span class="font-medium">₦{{ number_format($campaign->platform_fee_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-white/10">
                        <span class="font-semibold">Total budget</span>
                        <span class="font-bold text-trenakt-accent">₦{{ number_format($campaign->total_budget, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-4">Targeting</p>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-xs mb-1">Countries</p>
                        <p class="font-medium">{{ $countries->isNotEmpty() ? $countries->pluck('name')->join(', ') : 'All countries' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-xs mb-1">Age range</p>
                        <p class="font-medium">{{ $campaign->targeting?->min_age ?? 'N/A' }}&ndash;{{ $campaign->targeting?->max_age ?? 'N/A' }} years</p>
                    </div>
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-xs mb-1">Gender</p>
                        <p class="font-medium">{{ $genderLabel }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Approve confirmation --}}
    <x-modal name="approve-campaign">
        <div class="w-11 h-11 rounded-full bg-trenakt-success-light text-trenakt-success flex items-center justify-center mb-4">
            <x-icon name="check-circle" class="w-5.5 h-5.5" />
        </div>
        <h3 class="text-lg font-semibold mb-2">Approve this campaign?</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            ₦{{ number_format($campaign->total_budget, 2) }} moves from reserved to spent and the campaign goes live immediately. This cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500" wire:loading.attr="disabled" wire:target="approve">Cancel</button>
            <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve"
                class="bg-trenakt-success text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                <span wire:loading.remove wire:target="approve">Approve</span>
                <span wire:loading wire:target="approve">Approving...</span>
            </button>
        </div>
    </x-modal>

    {{-- Reject with reason --}}
    <x-modal name="reject-campaign">
        <div class="w-11 h-11 rounded-full bg-trenakt-danger/10 text-trenakt-danger flex items-center justify-center mb-4">
            <x-icon name="x-circle" class="w-5.5 h-5.5" />
        </div>
        <h3 class="text-lg font-semibold mb-2">Reject this campaign?</h3>
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
