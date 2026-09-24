<div>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Earning</p>
            <h1 class="text-2xl font-bold">Available tasks</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Complete a task and get paid once it's reviewed.</p>
        </div>
        <a href="{{ route('tasks.index') }}" wire:navigate class="inline-flex items-center justify-center border border-gray-200 dark:border-white/10 text-sm font-medium rounded-md px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-white/5 transition">
            My tasks
        </a>
    </div>

    @if (! $profileComplete)
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg">
            <x-empty-state icon="user" title="Complete your profile first"
                description="We use your name, phone, date of birth, and country to match you to tasks you're eligible for.">
                <a href="{{ route('profile.edit') }}" wire:navigate class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2">
                    Complete profile
                </a>
            </x-empty-state>
        </div>
    @else
        @if ($activationRequired)
            <div class="flex items-start gap-3 bg-trenakt-warning-light text-trenakt-warning rounded-lg p-4 mb-5">
                <x-icon name="shield" class="w-5 h-5 shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-semibold">Activate your account to accept tasks</p>
                    <p class="text-sm mt-0.5">Browse freely. You'll be asked to pay a one-time <x-currency :amount="$activationFeeAmount" /> fee the first time you start a task.</p>
                </div>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 mb-5">
            <div class="relative flex-1">
                <x-icon name="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search tasks"
                    class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-primary">
            </div>
            <div class="sm:w-56">
                <x-select model="category" :options="['all' => 'All categories'] + $categories->toArray()" :selected="$category" />
            </div>
        </div>

        <div wire:loading.remove wire:target="search,category">
            @if ($campaigns->isEmpty())
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg">
                    <x-empty-state icon="briefcase" title="No tasks available right now"
                        description="Check back soon, or adjust your search and filters." />
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($campaigns as $campaign)
                        @php
                            $spotsLeft = $campaign->target_participants - $campaign->active_submissions_count;
                        @endphp
                        <article class="min-w-0 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 flex flex-col">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-400 dark:text-white/40 mb-1">{{ $campaign->category->name }}</p>
                                    <h2 class="font-semibold truncate">{{ $campaign->title }}</h2>
                                </div>
                                <span class="shrink-0 text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5 bg-trenakt-primary/10 text-trenakt-primary">
                                    {{ $spotsLeft }} {{ \Illuminate\Support\Str::plural('spot', $spotsLeft) }} left
                                </span>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4">{{ $campaign->description ?: 'No description provided.' }}</p>

                            <div class="mt-auto flex items-center justify-between gap-3 pt-3 border-t border-gray-100 dark:border-white/10">
                                <span class="font-bold text-trenakt-primary"><x-currency :amount="$campaign->rate_per_participant" /></span>
                                <button type="button" wire:click="viewTask({{ $campaign->id }})"
                                    @click="$dispatch('open-modal', { name: 'task-details' })"
                                    class="text-sm font-medium bg-trenakt-primary text-white rounded-md px-4 py-2 hover:opacity-90 transition">
                                    View task
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        <div wire:loading.grid wire:target="search,category" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" style="gap: 1rem;">
            @for ($i = 0; $i < 6; $i++)
                <x-skeleton-card class="min-h-[170px]" />
            @endfor
        </div>
    @endif

    <x-modal name="task-details" maxWidth="lg">
        <div wire:loading.block wire:target="viewTask" class="animate-pulse space-y-5">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/4"></div>
                    <div class="h-5 bg-gray-200 dark:bg-white/10 rounded w-2/3"></div>
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-1/5"></div>
                </div>
            </div>
            <div class="space-y-2">
                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-5/6"></div>
                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
            </div>
            <div class="h-10 bg-gray-200 dark:bg-white/10 rounded w-1/3 ml-auto"></div>
        </div>

        <div wire:loading.remove wire:target="viewTask">
        @if ($selectedCampaign)
            <div class="flex items-start justify-between gap-4 mb-5">
                <div class="min-w-0">
                    <p class="text-xs text-gray-400 dark:text-white/40 mb-1">{{ $selectedCampaign->category->name }}</p>
                    <h2 class="text-lg font-semibold truncate">{{ $selectedCampaign->title }}</h2>
                    <p class="text-sm font-bold text-trenakt-primary mt-1"><x-currency :amount="$selectedCampaign->rate_per_participant" /></p>
                </div>
                <button type="button" @click="$dispatch('close-modal')" class="text-gray-400 hover:text-trenakt-dark dark:hover:text-white shrink-0" title="Close">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <div class="max-h-[65vh] overflow-y-auto pr-1 space-y-5 scrollbar-brand">
                @if ($rejectionNote)
                    <div class="flex items-start gap-3 bg-trenakt-danger/10 text-trenakt-danger rounded-lg p-3.5">
                        <x-icon name="alert-triangle" class="w-4.5 h-4.5 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-xs font-semibold">Your last attempt was rejected</p>
                            <p class="text-xs mt-0.5">{{ $rejectionNote->rejection_reason }}</p>
                        </div>
                    </div>
                @endif

                @if (count($steps) > 0)
                    <div class="bg-trenakt-primary-light dark:bg-white/5 rounded-lg p-3.5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-trenakt-primary mb-2">Steps to complete this task</p>
                        <ol class="space-y-2">
                            @foreach ($steps as $index => $step)
                                <li class="text-sm flex items-start gap-2.5">
                                    <span class="shrink-0 w-5 h-5 rounded-full bg-trenakt-primary text-white text-[11px] font-semibold flex items-center justify-center mt-0.5">{{ $index + 1 }}</span>
                                    <span class="text-trenakt-dark dark:text-white">{{ $step }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif

                @if ($businessInfo->isNotEmpty())
                    <div class="bg-gray-50 dark:bg-white/5 rounded-lg p-3.5">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-2">Resources</p>
                        <div class="space-y-2">
                            @foreach ($businessInfo as $answer)
                                <div class="text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">{{ $answer->field->label }}:</span>
                                    @if ($answer->field->type === 'url')
                                        <a href="{{ $answer->value }}" target="_blank" rel="noopener noreferrer" class="text-trenakt-primary font-medium hover:underline break-all">{{ $answer->value }}</a>
                                    @elseif ($answer->field->type === 'file')
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($answer->value) }}" target="_blank" rel="noopener noreferrer" class="text-trenakt-primary font-medium hover:underline">View file</a>
                                    @else
                                        <span class="font-medium text-trenakt-dark dark:text-white">{{ $answer->value }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if (! $started)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-2">Description</p>
                        <p class="text-sm whitespace-pre-wrap">{{ $selectedCampaign->description ?: 'No description provided.' }}</p>
                    </div>

                    @if ($participantFields->isNotEmpty() || $customFields->isNotEmpty())
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-2">You'll be asked for</p>
                            <ul class="space-y-1.5">
                                @foreach ($participantFields as $field)
                                    <li class="text-sm flex items-center gap-2">
                                        <span class="w-1 h-1 rounded-full bg-gray-400 shrink-0"></span>
                                        {{ $field->label }} @if (! $field->is_required) <span class="text-gray-400 text-xs">(optional)</span> @endif
                                    </li>
                                @endforeach
                                @foreach ($customFields as $field)
                                    <li class="text-sm flex items-center gap-2">
                                        <span class="w-1 h-1 rounded-full bg-gray-400 shrink-0"></span>
                                        {{ $field->label }} @if (! $field->is_required) <span class="text-gray-400 text-xs">(optional)</span> @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($activationRequired)
                        <div class="flex items-start gap-3 bg-trenakt-warning-light text-trenakt-warning rounded-lg p-3.5">
                            <x-icon name="shield" class="w-4.5 h-4.5 shrink-0 mt-0.5" />
                            <div>
                                <p class="text-xs font-semibold">Activate your account to start</p>
                                <p class="text-xs mt-0.5">A one-time <x-currency :amount="$activationFeeAmount" /> activation fee unlocks tasks for good, no more prompts after this.</p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Close</button>
                            <button type="button" wire:click="activateAccount" wire:loading.attr="disabled" wire:target="activateAccount"
                                class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                                <span wire:loading.remove wire:target="activateAccount">Activate for <x-currency :amount="$activationFeeAmount" /></span>
                                <span wire:loading wire:target="activateAccount">Connecting...</span>
                            </button>
                        </div>
                    @else
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Close</button>
                            <button type="button" wire:click="startTask" class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2">
                                Start task
                            </button>
                        </div>
                    @endif
                @else
                    <form wire:submit="submit" class="space-y-4">
                        @foreach ($participantFields as $field)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                                    {{ $field->label }} @if (! $field->is_required) <span class="text-gray-400">(optional)</span> @endif
                                </label>
                                @if ($field->type === 'textarea')
                                    <textarea wire:model="answers.{{ $field->id }}" rows="3"
                                        class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary"></textarea>
                                @elseif ($field->type === 'file')
                                    <input wire:model="answers.{{ $field->id }}" type="file"
                                        class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-trenakt-primary/10 file:text-trenakt-primary file:text-sm">
                                @else
                                    <input wire:model="answers.{{ $field->id }}" type="{{ $field->type === 'number' ? 'number' : ($field->type === 'url' ? 'url' : 'text') }}"
                                        class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                                @endif
                                @error('answers.' . $field->id) <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
                            </div>
                        @endforeach

                        @foreach ($customFields as $field)
                            <div>
                                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">
                                    {{ $field->label }} @if (! $field->is_required) <span class="text-gray-400">(optional)</span> @endif
                                </label>
                                @if ($field->type === 'textarea')
                                    <textarea wire:model="answers.{{ $field->field_key }}" rows="3"
                                        class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary"></textarea>
                                @elseif ($field->type === 'file')
                                    <input wire:model="answers.{{ $field->field_key }}" type="file"
                                        class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-trenakt-primary/10 file:text-trenakt-primary file:text-sm">
                                @else
                                    <input wire:model="answers.{{ $field->field_key }}" type="{{ $field->type === 'number' ? 'number' : ($field->type === 'url' ? 'url' : 'text') }}"
                                        class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                                @endif
                                @error('answers.' . $field->field_key) <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
                            </div>
                        @endforeach

                        @if ($participantFields->isEmpty() && $customFields->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No additional details are needed, just submit to complete this task.</p>
                        @endif

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" wire:click="$set('started', false)" wire:loading.attr="disabled" wire:target="submit" class="text-sm font-medium text-gray-500">Back</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                                class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                                <span wire:loading.remove wire:target="submit">Submit for review</span>
                                <span wire:loading wire:target="submit">Submitting...</span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        @endif
        </div>
    </x-modal>
</div>
