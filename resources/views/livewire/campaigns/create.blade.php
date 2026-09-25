<div>
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Promoting</p>
            <h1 class="text-2xl font-bold">Create campaign</h1>
        </div>
        <button type="button" @click="$dispatch('open-modal', { name: 'campaign-guidelines' })"
            class="inline-flex items-center gap-1.5 text-xs font-medium text-trenakt-primary hover:underline shrink-0 mt-1">
            <x-icon name="info-circle" class="w-3.5 h-3.5" />
            Posting guidelines
        </button>
    </div>

    <div class="lg:flex lg:gap-8 lg:items-start">
    <div class="flex flex-col gap-8 lg:w-2/3" style="row-gap: 2rem;">
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5">
            <h2 class="text-sm font-semibold">1. Category</h2>

            @if ($categories->isEmpty())
                <x-empty-state icon="briefcase" title="No categories available" description="There are no active campaign categories yet. Check back soon." />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($categories as $cat)
                        <button type="button" wire:click="$set('campaign_category_id', {{ $cat->id }})"
                            class="text-left border rounded-md p-4 transition
                                {{ $campaign_category_id === $cat->id ? 'border-trenakt-primary bg-trenakt-primary/5' : 'border-gray-200 dark:border-white/10 hover:border-gray-300' }}">
                            <p class="text-sm font-semibold text-trenakt-dark dark:text-white">{{ $cat->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1 line-clamp-2">{{ $cat->description }}</p>
                        </button>
                    @endforeach
                </div>
            @endif
            @error('campaign_category_id') <p class="text-xs text-trenakt-danger">{{ $message }}</p> @enderror
        </div>

        <div wire:loading.flex wire:target="campaign_category_id" class="w-full flex flex-col gap-8" style="row-gap: 2rem;">
            @foreach ([
                ['title' => '2. Campaign details', 'fields' => ['short', 'long']],
                ['title' => '3. Pricing & reach', 'fields' => ['range', 'range']],
                ['title' => '4. Information participants will need', 'fields' => ['short', 'long']],
            ] as $section)
                <div class="w-full bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5 animate-pulse">
                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-1/3"></div>

                    @foreach ($section['fields'] as $field)
                        @if ($field === 'range')
                            <div class="space-y-2">
                                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/4"></div>
                                <div class="h-2 bg-gray-200 dark:bg-white/10 rounded-full w-full"></div>
                                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/6"></div>
                            </div>
                        @elseif ($field === 'long')
                            <div class="space-y-2">
                                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/4"></div>
                                <div class="h-20 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
                            </div>
                        @else
                            <div class="space-y-2">
                                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/4"></div>
                                <div class="h-10 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach

            <div class="w-full bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5 animate-pulse">
                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-2/5"></div>
                <div class="h-10 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
                <div class="flex flex-wrap gap-2">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="h-8 w-20 bg-gray-200 dark:bg-white/10 rounded-full"></div>
                    @endfor
                </div>
            </div>
        </div>

        <div wire:loading.remove wire:target="campaign_category_id" class="flex flex-col gap-8" style="row-gap: 2rem;">
        @if ($category)
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5">
                <h2 class="text-sm font-semibold">2. Campaign details</h2>

                <div>
                    <label class="text-sm font-medium">Title</label>
                    <input wire:model="title" type="text" placeholder="e.g. Test our new checkout flow"
                        class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                    @error('title') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Description</label>
                    <textarea wire:model="description" rows="4" placeholder="Tell participants what you need from them"
                        class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary"></textarea>
                    @error('description') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="text-sm font-medium">Steps to complete this task (optional)</label>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Spell out exactly what a participant should do, in order, e.g. "Click the link above", "Sign up with your real email", "Take a screenshot of the confirmation page". We show these to participants as a numbered list instead of your description alone.</p>
                        </div>
                        <button type="button" wire:click="addStep" class="text-xs font-medium text-trenakt-primary shrink-0">+ Add step</button>
                    </div>

                    @if (count($steps) > 0)
                        <div class="space-y-2 mt-3">
                            @foreach ($steps as $index => $step)
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-gray-400 dark:text-white/40 w-5 shrink-0">{{ $index + 1 }}.</span>
                                    <input wire:model="steps.{{ $index }}" type="text" placeholder="e.g. Click the link above and sign up"
                                        class="w-full border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                                    <button type="button" wire:click="removeStep({{ $index }})" class="text-gray-300 hover:text-trenakt-danger transition shrink-0">
                                        <x-icon name="x" class="w-4 h-4" />
                                    </button>
                                </div>
                                @error('steps.' . $index) <p class="text-xs text-trenakt-danger ml-7">{{ $message }}</p> @enderror
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex items-start gap-3 border-t border-gray-100 dark:border-white/10 pt-4">
                    <x-toggle model="allow_admin_edit" :checked="$allow_admin_edit" />
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium">Allow Trenakt to refine my instructions before approval</label>
                            <button type="button" @click="$dispatch('open-modal', { name: 'admin-edit-info' })" class="text-xs font-medium text-trenakt-primary hover:underline shrink-0">Why?</button>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-white/40 mt-1">If checked, our review team may lightly tighten your title, description, or steps for clarity before approving. You'll always be able to see what changed.</p>
                    </div>
                </div>
            </div>

            @if ($category->supports_post_modes)
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5"
                    x-data="{ mode: $wire.entangle('task_mode'), selectedPlatforms: $wire.entangle('platforms') }">
                    <h2 class="text-sm font-semibold">3. How should participants share this?</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button type="button" @click="mode = 'reshare'"
                            :class="mode === 'reshare' ? 'border-trenakt-primary bg-trenakt-primary/5' : 'border-gray-200 dark:border-white/10 hover:border-gray-300'"
                            class="text-left border rounded-md p-4 transition">
                            <p class="text-sm font-semibold text-trenakt-dark dark:text-white">Reshare an existing post</p>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Participants share a post you already have live. Uses the link submission flow you're used to.</p>
                        </button>
                        <button type="button" @click="mode = 'post_own_content'"
                            :class="mode === 'post_own_content' ? 'border-trenakt-primary bg-trenakt-primary/5' : 'border-gray-200 dark:border-white/10 hover:border-gray-300'"
                            class="text-left border rounded-md p-4 transition">
                            <p class="text-sm font-semibold text-trenakt-dark dark:text-white">Post on their own page</p>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1">You supply the caption and a flyer, image, or video. Participants post it as their own content.</p>
                        </button>
                    </div>
                    @error('task_mode') <p class="text-xs text-trenakt-danger">{{ $message }}</p> @enderror

                    <div x-show="mode === 'post_own_content'" x-cloak class="space-y-4 pt-2 border-t border-gray-100 dark:border-white/10">
                        <div>
                            <label class="text-sm font-medium">Caption / text to post</label>
                            <textarea wire:model="post_content_text" rows="3" placeholder="Exactly what you want participants to post as the caption"
                                class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary"></textarea>
                            @error('post_content_text') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-medium">Flyer, image, or video</label>
                            <input wire:model="post_content_media" type="file" accept="image/*,video/*"
                                class="w-full mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Up to 20MB. Images are automatically compressed for you; keep the file well under that if you can, for a faster upload.</p>
                            <div wire:loading wire:target="post_content_media" class="text-xs text-trenakt-primary mt-1">Uploading...</div>
                            @error('post_content_media') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-100 dark:border-white/10">
                        <label class="text-sm font-medium mb-1.5 block">Where should this be posted?</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach (\App\Livewire\Campaigns\Create::PLATFORMS as $key => $label)
                                <button type="button"
                                    @click="selectedPlatforms.includes('{{ $key }}') ? selectedPlatforms.splice(selectedPlatforms.indexOf('{{ $key }}'), 1) : selectedPlatforms.push('{{ $key }}')"
                                    :class="selectedPlatforms.includes('{{ $key }}') ? 'border-trenakt-primary bg-trenakt-primary/10 text-trenakt-primary' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400'"
                                    class="text-xs font-medium border rounded-full px-3 py-1.5 transition">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        @error('platforms') <p class="text-xs text-trenakt-danger mt-2">{{ $message }}</p> @enderror
                        @if ((float) $category->platform_bonus_amount > 0)
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-2">Selecting more than one platform adds ₦{{ number_format($category->platform_bonus_amount, 2) }} to the participant rate for each platform beyond the first.</p>
                        @endif
                    </div>

                    <div x-show="mode === 'reshare'" x-cloak class="space-y-4 pt-2 border-t border-gray-100 dark:border-white/10">
                        <div>
                            <p class="text-sm font-medium">Link to the post to reshare, per platform</p>
                            <p class="text-xs text-gray-400 dark:text-white/40 mt-1">The same post has a different link on each platform, so give the link for each one you selected above.</p>
                        </div>
                        @foreach (\App\Livewire\Campaigns\Create::PLATFORMS as $key => $label)
                            <div x-show="selectedPlatforms.includes('{{ $key }}')" x-cloak>
                                <label class="text-sm font-medium">{{ $label }} post link</label>
                                <input wire:model="platformSourceLinks.{{ $key }}" type="url" placeholder="https://..."
                                    class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                                @error('platformSourceLinks.' . $key) <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5">
                <h2 class="text-sm font-semibold">4. Pricing & reach</h2>

                <x-range-slider label="Rate per participant" model="rate_per_participant" :min="(float) $category->min_rate" :max="(float) $category->max_rate" :step="50" :value="$rate_per_participant" suffix=" NGN" />
                @error('rate_per_participant') <p class="text-xs text-trenakt-danger">{{ $message }}</p> @enderror

                <x-range-slider label="Target participants" model="target_participants" :min="(int) $category->min_participants" :max="(int) ($category->max_participants ?: 500)" :step="1" :value="$target_participants" suffix=" people" />
                @error('target_participants') <p class="text-xs text-trenakt-danger">{{ $message }}</p> @enderror
            </div>

            {{-- $businessFields already excludes the category's generic
                url-type field for any post-mode category (supports_post_modes)
                - see Create::businessFieldsFor(). That's based on the
                category alone, which is chosen via a real request (see the
                category-select buttons above), so it's already correct here
                with no extra reactivity needed - unlike task_mode/platforms,
                which are chosen client-side and need the $wire.entangle
                instant-update pattern used in card 3 above. --}}
            @if ($businessFields->isNotEmpty())
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5">
                    <h2 class="text-sm font-semibold">5. Information participants will need</h2>

                    @foreach ($businessFields as $field)
                        <div>
                            <label class="text-sm font-medium">{{ $field->label }} @if($field->is_required)<span class="text-trenakt-danger">*</span>@endif</label>

                            @if ($field->type === 'textarea')
                                <textarea wire:model="businessAnswers.{{ $field->id }}" rows="3"
                                    class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary"></textarea>
                            @elseif ($field->type === 'file')
                                <input wire:model="businessAnswers.{{ $field->id }}" type="file"
                                    class="w-full mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                            @elseif ($field->type === 'number')
                                <input wire:model="businessAnswers.{{ $field->id }}" type="number"
                                    class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                            @else
                                <input wire:model="businessAnswers.{{ $field->id }}" type="text"
                                    class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($category->requirementFields->where('fills_for', 'participant')->isNotEmpty())
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-4">
                    <div>
                        <h2 class="text-sm font-semibold">6. What participants will submit</h2>
                        <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Set for the "{{ $category->name }}" category and applied to every campaign in it. Participants fill these in when they complete the task, not you.</p>
                    </div>

                    <div class="space-y-2">
                        @foreach ($category->requirementFields->where('fills_for', 'participant') as $field)
                            <div class="flex items-center justify-between text-sm border border-gray-100 dark:border-white/10 rounded-md px-3 py-2">
                                <span class="text-trenakt-dark dark:text-white">{{ $field->label }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] uppercase tracking-wide bg-gray-100 dark:bg-white/5 text-gray-400 rounded-full px-2 py-0.5">{{ ucfirst($field->type) }}</span>
                                    @if ($field->is_required)
                                        <span class="text-[10px] uppercase tracking-wide bg-trenakt-primary/10 text-trenakt-primary rounded-full px-2 py-0.5">Required</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold">7. Anything else to ask? (optional)</h2>
                        <p class="text-xs text-gray-400 dark:text-white/40 mt-1">Add extra questions just for this campaign, on top of what the category already asks for.</p>
                    </div>
                    <button type="button" wire:click="addCustomField" class="text-xs font-medium text-trenakt-primary">+ Add question</button>
                </div>

                @foreach ($customFields as $index => $field)
                    <div class="border border-gray-100 dark:border-white/10 rounded-md p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 space-y-3">
                                <input wire:model="customFields.{{ $index }}.label" type="text" placeholder="Question, e.g. What device did you test on?"
                                    class="w-full border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">

                                <div class="grid grid-cols-2 gap-3 items-center">
                                    <x-select model="customFields.{{ $index }}.type" :options="['text' => 'Short text', 'textarea' => 'Long text', 'file' => 'File upload', 'url' => 'Link', 'number' => 'Number']" :selected="$field['type']" />
                                    <div class="flex items-center gap-2">
                                        <x-toggle model="customFields.{{ $index }}.is_required" :checked="$field['is_required']" />
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Required</span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" wire:click="removeCustomField({{ $index }})" class="text-gray-300 hover:text-trenakt-danger transition mt-1">
                                <x-icon name="x" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-5">
                <h2 class="text-sm font-semibold">8. Targeting</h2>

                <div>
                    <label class="text-sm font-medium mb-1.5 block">Countries</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($countries as $c)
                            <button type="button" wire:click="toggleCountry({{ $c->id }})"
                                class="text-xs font-medium border rounded-full px-3 py-1.5 transition
                                    {{ in_array($c->id, $country_ids) ? 'border-trenakt-primary bg-trenakt-primary/10 text-trenakt-primary' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400' }}">
                                {{ $c->name }}
                            </button>
                        @endforeach
                    </div>
                    @error('country_ids') <p class="text-xs text-trenakt-danger mt-2">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <x-range-slider label="Minimum age" model="min_age" :min="13" :max="80" :step="1" :value="$min_age" />
                    <x-range-slider label="Maximum age" model="max_age" :min="13" :max="80" :step="1" :value="$max_age" />
                </div>

                <x-select model="gender" :options="['any' => 'Any gender', 'male' => 'Male', 'female' => 'Female']" :selected="$gender" />
            </div>

            @php $insufficientBalance = $budget && $budget['total'] > $balance; @endphp

            @if ($insufficientBalance)
                <div class="bg-trenakt-danger/5 border border-trenakt-danger/20 rounded-lg p-4 flex items-center justify-between gap-4">
                    <p class="text-sm text-trenakt-danger">Your balance is short by ₦{{ number_format($budget['total'] - $balance, 2) }} for this campaign.</p>
                    <button type="button" @click="$dispatch('open-modal', { name: 'fund-wallet-inline' })" class="text-xs font-medium text-trenakt-danger underline shrink-0">Fund wallet</button>
                </div>
            @endif

            <div class="flex justify-end">
                <button type="button" @click="$dispatch('open-modal', { name: 'confirm-campaign-submit' })"
                    class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-6 py-2.5">
                    Submit for review
                </button>
            </div>
        @endif
        </div>
    </div>

    @if ($category && $budget)
        <div wire:loading.remove wire:target="campaign_category_id" class="hidden lg:block lg:w-1/3 space-y-6 sticky top-24">
            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30">Wallet balance</p>
                <p class="text-xl sm:text-2xl font-bold {{ $insufficientBalance ? 'text-trenakt-danger' : 'text-trenakt-primary' }}">₦{{ number_format($balance, 2) }}</p>
            </div>

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30">Budget</p>

                <div class="space-y-2 text-sm">
                    @if (($budget['platform_bonus'] ?? 0) > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400 dark:text-white/40">Rate per participant</span>
                            <span class="font-medium text-trenakt-dark dark:text-white">₦{{ number_format($budget['base_rate'], 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400 dark:text-white/40">Multi-platform bonus</span>
                            <span class="font-medium text-trenakt-dark dark:text-white">+₦{{ number_format($budget['platform_bonus'], 2) }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 dark:text-white/40">Participant payouts</span>
                        <span class="font-medium text-trenakt-dark dark:text-white">₦{{ number_format($budget['subtotal'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400 dark:text-white/40">Platform fee</span>
                        <span class="font-medium text-trenakt-dark dark:text-white">₦{{ number_format($budget['fee'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 dark:border-white/10 pt-2 mt-2">
                        <span class="text-gray-500 dark:text-gray-400 font-medium">Total to be held</span>
                        <span class="font-bold {{ $insufficientBalance ? 'text-trenakt-danger' : 'text-trenakt-primary' }}">₦{{ number_format($budget['total'], 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-6 space-y-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30">How this works</p>
                <p class="text-sm text-gray-500 dark:text-white/60">This amount is held from your wallet the moment you submit, not spent yet. An admin reviews your campaign, and only then does it actually go live and the hold turns into a real charge. If it's rejected, the full amount goes back to your available balance.</p>
            </div>
        </div>
    @endif

    <div wire:loading wire:target="campaign_category_id" class="hidden lg:block lg:w-1/3 space-y-6 sticky top-24">
        <x-skeleton-card class="w-full h-28" />
        <div class="w-full bg-white dark:bg-trenakt-surface-dark rounded-lg border border-gray-100 dark:border-white/10 p-6 space-y-4 animate-pulse">
            <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-1/3"></div>
            <div class="space-y-3">
                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-5/6"></div>
                <div class="h-3 bg-gray-200 dark:bg-white/10 rounded w-full"></div>
            </div>
        </div>
        <x-skeleton-card class="w-full h-36" />
    </div>
    </div>

    <x-modal name="fund-wallet-inline" maxWidth="md">
        <h3 class="text-lg font-semibold mb-2">Fund wallet</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">You'll be taken to your payment provider to complete this, then brought straight back here with everything you've filled in still in place.</p>

        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium">Amount (NGN)</label>
                <input wire:model="fundAmount" type="number" min="100" step="100"
                    class="w-full mt-1.5 border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2 text-sm focus:outline-none focus:border-trenakt-primary">
                @error('fundAmount') <p class="text-xs text-trenakt-danger mt-1">{{ $message }}</p> @enderror

                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach ([2000, 5000, 10000, 20000, 50000] as $preset)
                        <button type="button" wire:click="$set('fundAmount', {{ $preset }})"
                            class="text-xs font-medium border border-gray-200 dark:border-white/10 rounded-full px-3 py-1 text-gray-500 dark:text-gray-400 hover:border-trenakt-primary hover:text-trenakt-primary transition">
                            ₦{{ number_format($preset) }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="text-sm font-medium mb-1.5 block">Pay with</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" wire:click="selectFundGateway('paystack')"
                        class="border rounded-md px-4 py-3 text-sm font-medium text-left transition
                            {{ $fundGateway === 'paystack' ? 'border-trenakt-primary bg-trenakt-primary/5 text-trenakt-primary' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400' }}">
                        Paystack
                    </button>
                    <button type="button" wire:click="selectFundGateway('flutterwave')"
                        class="border rounded-md px-4 py-3 text-sm font-medium text-left transition
                            {{ $fundGateway === 'flutterwave' ? 'border-trenakt-primary bg-trenakt-primary/5 text-trenakt-primary' : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400' }}">
                        Flutterwave
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
            <button type="button" wire:click="fundWallet" wire:loading.attr="disabled" wire:target="fundWallet"
                class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                <span wire:loading.remove wire:target="fundWallet">Continue to payment</span>
                <span wire:loading wire:target="fundWallet">Connecting...</span>
            </button>
        </div>
    </x-modal>

    <x-modal name="campaign-guidelines" maxWidth="md">
        <div class="flex items-start justify-between gap-4 mb-4">
            <h3 class="text-lg font-semibold">Before you create a campaign</h3>
            <button type="button" wire:click="acknowledgeGuidelines" @click="$dispatch('close-modal')" class="text-gray-400 hover:text-trenakt-dark dark:hover:text-white shrink-0" title="Close">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <div class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
            <div>
                <p class="font-medium text-trenakt-success mb-1">Do</p>
                <ul class="space-y-1 text-gray-500 dark:text-gray-400">
                    <li>Describe a real product, service, or action for participants to genuinely engage with.</li>
                    <li>Write clear, honest steps that match what you're actually asking participants to do.</li>
                    <li>Fund your wallet with money you're prepared to spend on real campaign payouts.</li>
                </ul>
            </div>
            <div>
                <p class="font-medium text-trenakt-danger mb-1">Don't</p>
                <ul class="space-y-1 text-gray-500 dark:text-gray-400">
                    <li>Run scams, Ponzi or pyramid schemes, or anything designed to defraud participants.</li>
                    <li>Post nudity, sexual content, or anything else that violates the law or another party's rights.</li>
                    <li>Ask participants to do something different from what your campaign says.</li>
                </ul>
            </div>
            <p class="text-xs text-gray-400 dark:text-white/40 pt-1">
                An account found doing any of this may be suspended or permanently blocked, and money already funded to your wallet cannot be withdrawn.
            </p>
        </div>

        <div class="flex justify-end mt-6">
            <button type="button" wire:click="acknowledgeGuidelines" @click="$dispatch('close-modal')" class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2">I understand</button>
        </div>
    </x-modal>

    <x-modal name="admin-edit-info" maxWidth="md">
        <h3 class="text-lg font-semibold mb-2">Why we ask this</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Sometimes campaign instructions aren't clear enough for participants to follow correctly, even when you know exactly what you mean. That leads to submissions that don't match what you wanted, wasted budget for you, and participants who don't get paid for work they thought was right.</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">If you allow it, our review team may lightly tighten the wording of your title, description, or steps before approving, never your price, targeting, or what you're asking participants to do. You'll always be able to see exactly what was changed.</p>
        <p class="text-sm text-gray-500 dark:text-gray-400">If you leave this off, we'll only approve or reject your campaign as written. A rejected campaign can always be edited and resubmitted by you.</p>
        <div class="flex justify-end mt-6">
            <button type="button" @click="$dispatch('close-modal')" class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2">Got it</button>
        </div>
    </x-modal>

    <x-modal name="confirm-campaign-submit" maxWidth="md">
        <h3 class="text-lg font-semibold mb-2">Submit this campaign?</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">₦{{ number_format($budget['total'] ?? 0, 2) }} will be held from your wallet once you submit. Once this campaign is approved, it cannot be deleted or paused, so double-check the details above before continuing.</p>
        <div class="flex justify-end gap-3">
            <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
            <button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit"
                class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2 disabled:opacity-60">
                <span wire:loading.remove wire:target="submit">Yes, submit</span>
                <span wire:loading wire:target="submit">Submitting...</span>
            </button>
        </div>
    </x-modal>
</div>