<div>
    <a href="{{ route('admin.categories.index') }}" wire:navigate
        class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-trenakt-dark dark:hover:text-white transition mb-6">
        <x-icon name="arrow-left" class="w-4 h-4" />
        Back to categories
    </a>

    <h1 class="text-2xl font-bold mb-6">{{ $categoryId ? 'Edit category' : 'New category' }}</h1>

    <div class="lg:flex lg:gap-8 lg:items-start">
    <div class="space-y-8 lg:w-2/3">
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5">
            <div>
                <label class="text-sm font-medium">Name</label>
                <input wire:model="name" type="text" placeholder="e.g. Product Testing"
                    class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                @error('name') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium">Description</label>
                <textarea wire:model="description" rows="3"
                    class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <x-range-slider label="Minimum rate" model="min_rate" :min="0" :max="10000" :step="100" :value="$min_rate" suffix=" NGN" />
                <x-range-slider label="Maximum rate" model="max_rate" :min="0" :max="20000" :step="100" :value="$max_rate" suffix=" NGN" />
            </div>
            @error('max_rate') <p class="text-xs text-trenakt-danger">{{ $message }}</p> @enderror

            <x-range-slider label="Platform fee" model="platform_fee_percentage" :min="0" :max="50" :step="1" :value="$platform_fee_percentage" suffix="%" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <x-range-slider label="Minimum participants" model="min_participants" :min="1" :max="200" :step="1" :value="$min_participants" suffix=" people" />

                <div>
                    <label class="text-sm font-medium">Maximum participants</label>
                    <input wire:model="max_participants" type="number" min="1" placeholder="No maximum"
                        class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Leave blank for no upper limit.</p>
                </div>
            </div>
            @error('max_participants') <p class="text-xs text-trenakt-danger">{{ $message }}</p> @enderror

            <div class="flex items-center justify-between pt-2">
                <span class="text-sm font-medium">Active</span>
                <x-toggle model="is_active" :checked="$is_active" />
            </div>
        </div>

        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold">Reshare vs. post-on-own-page</h2>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Lets a business choose whether participants reshare an existing post, or post the business's own supplied content instead. Only relevant for social-sharing categories.</p>
                </div>
                <x-toggle model="supports_post_modes" :checked="$supports_post_modes" />
            </div>

            @if ($supports_post_modes)
                <div class="pt-2 border-t border-gray-100 dark:border-white/10">
                    <label class="text-sm font-medium">Bonus per extra platform</label>
                    <div class="relative mt-1.5">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">₦</span>
                        <input wire:model="platform_bonus_amount" type="number" min="0" step="10"
                            class="w-full border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md pl-7 pr-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                    </div>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Added to the participant rate for each social platform a business selects beyond the first (e.g. selecting 3 platforms adds this amount twice). Set to 0 for no bonus.</p>
                    @error('platform_bonus_amount') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-white/10">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium">Monitor the post before paying out</h3>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Instead of paying the reward the moment a submission is approved, hold it and watch the participant's link. If it's deleted or made private before the window ends, the reward is never released.</p>
                        </div>
                        <x-toggle model="requires_monitoring" :checked="$requires_monitoring" />
                    </div>

                    @if ($requires_monitoring)
                        <div class="mt-4">
                            <label class="text-sm font-medium">Monitoring window</label>
                            <div class="grid grid-cols-2 gap-3 mt-1.5">
                                <input wire:model="monitoring_duration_value" type="number" min="1"
                                    class="w-full border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                                <x-select model="monitoring_duration_unit" :options="['minutes' => 'Minutes', 'hours' => 'Hours']" :selected="$monitoring_duration_unit" />
                            </div>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1">How long after approval a submission's reward stays on hold. Participants are told this when their submission is approved, and again if the reward is released or forfeited.</p>
                            @error('monitoring_duration_value') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold">Requirement fields</h2>
                <button type="button" wire:click="addField" class="text-xs font-medium text-trenakt-primary">+ Add field</button>
            </div>

            <div class="space-y-4">
                @foreach ($requirementFields as $index => $field)
                    <div class="border border-gray-100 dark:border-white/10 rounded-md p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 space-y-3">
                                <input wire:model="requirementFields.{{ $index }}.label" type="text" placeholder="Field label, e.g. Upload a screenshot"
                                    class="w-full border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">

                                <x-select model="requirementFields.{{ $index }}.fills_for" :options="['business' => 'Business fills this (creating the campaign)', 'participant' => 'Participant fills this (submitting their work)']" :selected="$field['fills_for']" />

                                <div class="grid grid-cols-2 gap-3 items-center">
                                    <x-select model="requirementFields.{{ $index }}.type" :options="$fieldTypes" :selected="$field['type']" />
                                    <div class="flex items-center gap-2">
                                        <x-toggle model="requirementFields.{{ $index }}.is_required" :checked="$field['is_required']" />
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Required</span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" wire:click="removeField({{ $index }})" class="text-gray-300 hover:text-trenakt-danger transition mt-1">
                                <x-icon name="x" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.categories.index') }}" wire:navigate class="text-sm font-medium text-gray-500 dark:text-gray-400 px-4 py-2.5">Cancel</a>
            <button type="button" wire:click="save" wire:loading.attr="disabled"
                class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-6 py-2.5 disabled:opacity-60">
                <span wire:loading.remove>{{ $categoryId ? 'Save changes' : 'Create category' }}</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </div>

    <div class="hidden lg:block lg:w-1/3 space-y-6 sticky top-24">
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30">Preview</p>

            <div>
                <p class="text-sm font-semibold text-trenakt-dark dark:text-white">{{ $name ?: 'Untitled category' }}</p>
                <p class="text-xs text-gray-400 dark:text-white/40 mt-1">{{ $description ?: 'No description yet.' }}</p>
            </div>

            <div class="border-t border-gray-100 dark:border-white/10 pt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-400 dark:text-white/40">Rate range</span>
                    <span class="font-medium text-trenakt-dark dark:text-white">{{ number_format($min_rate) }} – {{ number_format($max_rate) }} NGN</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400 dark:text-white/40">Platform fee</span>
                    <span class="font-medium text-trenakt-dark dark:text-white">{{ $platform_fee_percentage }}%</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400 dark:text-white/40">Status</span>
                    <span class="font-medium {{ $is_active ? 'text-trenakt-primary' : 'text-gray-400 dark:text-white/40' }}">{{ $is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400 dark:text-white/40">Participants</span>
                    <span class="font-medium text-trenakt-dark dark:text-white">{{ $min_participants }}{{ $max_participants ? ' – ' . $max_participants : '+' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-400 dark:text-white/40">Requirement fields</span>
                    <span class="font-medium text-trenakt-dark dark:text-white">{{ count($requirementFields) }}</span>
                </div>
                @if ($supports_post_modes)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 dark:text-white/40">Platform bonus</span>
                        <span class="font-medium text-trenakt-dark dark:text-white">+₦{{ number_format($platform_bonus_amount) }} / extra platform</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 dark:text-white/40">Reward monitoring</span>
                        <span class="font-medium {{ $requires_monitoring ? 'text-trenakt-primary' : 'text-gray-400 dark:text-white/40' }}">
                            {{ $requires_monitoring ? $monitoring_duration_value . ' ' . $monitoring_duration_unit . ' hold' : 'Off' }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30">How this works</p>

            <div class="space-y-4">
                <div class="flex gap-3">
                    <div class="w-6 h-6 rounded-full bg-trenakt-primary/10 text-trenakt-primary text-xs font-semibold flex items-center justify-center shrink-0">1</div>
                    <p class="text-sm text-gray-500 dark:text-white/60"><span class="font-medium text-trenakt-dark dark:text-white">Set the rate range.</span> Businesses choose a rate per participant between your minimum and maximum when they create a campaign in this category.</p>
                </div>
                <div class="flex gap-3">
                    <div class="w-6 h-6 rounded-full bg-trenakt-primary/10 text-trenakt-primary text-xs font-semibold flex items-center justify-center shrink-0">2</div>
                    <p class="text-sm text-gray-500 dark:text-white/60"><span class="font-medium text-trenakt-dark dark:text-white">Platform fee</span> is added on top of what the business pays and is what Trenakt earns from campaigns in this category.</p>
                </div>
                <div class="flex gap-3">
                    <div class="w-6 h-6 rounded-full bg-trenakt-primary/10 text-trenakt-primary text-xs font-semibold flex items-center justify-center shrink-0">3</div>
                    <p class="text-sm text-gray-500 dark:text-white/60"><span class="font-medium text-trenakt-dark dark:text-white">Requirement fields</span> show up on the campaign form for every business using this category, and on the submission form for every participant. Add as many as this category needs, no code required.</p>
                </div>
                <div class="flex gap-3">
                    <div class="w-6 h-6 rounded-full bg-trenakt-primary/10 text-trenakt-primary text-xs font-semibold flex items-center justify-center shrink-0">4</div>
                    <p class="text-sm text-gray-500 dark:text-white/60"><span class="font-medium text-trenakt-dark dark:text-white">Inactive categories</span> stay hidden from businesses but keep their existing campaigns and data intact.</p>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>