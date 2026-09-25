<?php

namespace App\Livewire\Campaigns;

use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\CampaignCustomField;
use App\Models\CampaignTargeting;
use App\Models\Country;
use App\Services\CampaignService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\CampaignRequirementAnswer;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?int $campaign_category_id = null;
    public string $title = '';
    public string $description = '';
    public array $steps = [];
    public float $rate_per_participant = 0;
    public int $target_participants = 0;
    public array $businessAnswers = [];
    public array $customFields = [];
    public array $country_ids = [];
    public ?int $min_age = 18;
    public ?int $max_age = 65;
    public string $gender = 'any';
    public bool $allow_admin_edit = false;

    /**
     * Only ever read/shown when the selected category's supports_post_modes
     * is on - see the migration that adds it. platforms holds keys from
     * PLATFORMS below (facebook/instagram/twitter/tiktok); task_mode is
     * 'reshare' or 'post_own_content'.
     */
    public const PLATFORMS = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'twitter' => 'X (Twitter)',
        'tiktok' => 'TikTok',
    ];

    public ?string $task_mode = null;
    public array $platforms = [];
    public string $post_content_text = '';
    public $post_content_media = null;

    /**
     * platform => the URL of the business's own already-live post to
     * reshare on that platform, e.g. ['facebook' => 'https://fb.com/...'].
     * Only meaningful for task_mode === 'reshare' - the same post has a
     * different URL on each platform, so one generic link can't cover
     * more than one selected platform. Not used for post_own_content,
     * where the business supplies post_content_text/post_content_media
     * instead (the same caption/media works across every platform since
     * participants post it themselves, so there's no per-platform link).
     */
    public array $platformSourceLinks = [];

    public float $fundAmount = 5000;
    public string $fundGateway = 'paystack';

    public function mount(): void
    {
        $this->country_ids = Country::where('is_active', true)->pluck('id')->toArray();

        if (session()->has('campaign_draft')) {
            foreach (session()->pull('campaign_draft') as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        if (session()->has('toast')) {
            $toast = session('toast');
            $this->dispatch('toast', type: $toast['type'], message: $toast['message']);
        }

        if (! auth()->user()->campaign_guidelines_acknowledged_at) {
            $this->dispatch('open-modal', name: 'campaign-guidelines');
        }
    }

    /**
     * Closing the guidelines modal (the X icon or "I understand") both
     * dismiss it and mark it acknowledged - there's no separate "don't
     * show this again" checkbox, dismissing IS not showing it again.
     * Stored on the user row (not browser storage) so it persists across
     * devices and doubles as a record they were shown the rules.
     */
    public function acknowledgeGuidelines(): void
    {
        if (! auth()->user()->campaign_guidelines_acknowledged_at) {
            auth()->user()->forceFill(['campaign_guidelines_acknowledged_at' => now()])->save();
        }

        $this->dispatch('close-modal', name: 'campaign-guidelines');
    }

    public function updatedCampaignCategoryId($value): void
    {
        $category = CampaignCategory::with('requirementFields')->find($value);

        if (! $category) {
            return;
        }

        $this->rate_per_participant = (float) $category->min_rate;
        $this->target_participants = (int) $category->min_participants;

        $this->businessAnswers = [];
        foreach ($category->requirementFields->where('fills_for', 'business') as $field) {
            $this->businessAnswers[$field->id] = null;
        }

        $this->task_mode = null;
        $this->platforms = [];
        $this->post_content_text = '';
        $this->post_content_media = null;
        $this->platformSourceLinks = [];
    }

    public function selectTaskMode(string $mode): void
    {
        $this->task_mode = $mode;
    }

    public function togglePlatform(string $platform): void
    {
        if (! array_key_exists($platform, self::PLATFORMS)) {
            return;
        }

        if (in_array($platform, $this->platforms, true)) {
            $this->platforms = array_values(array_diff($this->platforms, [$platform]));
        } else {
            $this->platforms[] = $platform;
        }
    }

    public function addCustomField(): void
    {
        $this->customFields[] = [
            'label' => '',
            'type' => 'text',
            'is_required' => true,
        ];
    }

    public function removeCustomField(int $index): void
    {
        unset($this->customFields[$index]);
        $this->customFields = array_values($this->customFields);
    }

    public function addStep(): void
    {
        $this->steps[] = '';
    }

    public function removeStep(int $index): void
    {
        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);
    }

    public function toggleCountry(int $countryId): void
    {
        if (in_array($countryId, $this->country_ids)) {
            $this->country_ids = array_values(array_diff($this->country_ids, [$countryId]));
        } else {
            $this->country_ids[] = $countryId;
        }
    }

    public function selectFundGateway(string $gateway): void
    {
        $this->fundGateway = $gateway;
    }

    public function fundWallet(
        \App\Services\Payments\WalletFundingService $fundingService,
        \App\Services\Payments\PaystackService $paystack,
        \App\Services\Payments\FlutterwaveService $flutterwave
    ) {
        $this->validate([
            'fundAmount' => ['required', 'numeric', 'min:100'],
            'fundGateway' => ['required', 'in:paystack,flutterwave'],
        ]);

        $fundingRequest = $fundingService->initiate(auth()->id(), $this->fundGateway, $this->fundAmount);
        $callbackUrl = route('wallet.callback', ['gateway' => $this->fundGateway]);

        try {
            $init = $this->fundGateway === 'paystack'
                ? $paystack->initialize(auth()->user()->email, $this->fundAmount, $callbackUrl, $fundingRequest->reference)
                : $flutterwave->initialize(auth()->user()->email, $this->fundAmount, $callbackUrl, $fundingRequest->reference);
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Could not connect to the payment gateway. Please try again.');
            return;
        }

        if (empty($init['authorization_url'])) {
            $this->dispatch('toast', type: 'error', message: 'The payment gateway did not return a payment link. Please try again.');
            return;
        }

        session([
            'wallet_return_to' => route('campaigns.create'),
            'campaign_draft' => [
                'campaign_category_id' => $this->campaign_category_id,
                'title' => $this->title,
                'description' => $this->description,
                'steps' => $this->steps,
                'rate_per_participant' => $this->rate_per_participant,
                'target_participants' => $this->target_participants,
                'customFields' => $this->customFields,
                'country_ids' => $this->country_ids,
                'min_age' => $this->min_age,
                'max_age' => $this->max_age,
                'gender' => $this->gender,
                'allow_admin_edit' => $this->allow_admin_edit,
                'task_mode' => $this->task_mode,
                'platforms' => $this->platforms,
                'post_content_text' => $this->post_content_text,
                'platformSourceLinks' => $this->platformSourceLinks,
                // post_content_media (an uploaded file) can't survive a
                // session round-trip the way these plain values can - same
                // limitation businessAnswers file uploads already have
                // here, so it's simply not preserved. The business re-
                // attaches it after returning from payment.
            ],
        ]);

        return redirect()->away($init['authorization_url']);
    }

    public function submit(CampaignService $campaignService)
    {
        $this->validate([
            'campaign_category_id' => ['required', 'exists:campaign_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'rate_per_participant' => ['required', 'numeric', 'min:0'],
            'target_participants' => ['required', 'integer', 'min:1'],
            'country_ids' => ['required', 'array', 'min:1'],
            'gender' => ['required', 'in:any,male,female'],
            'customFields.*.label' => ['nullable', 'string', 'max:255'],
            'customFields.*.type' => ['nullable', 'in:text,textarea,file,url,number'],
            'steps.*' => ['nullable', 'string', 'max:500'],
        ]);

        $category = CampaignCategory::with('requirementFields')->find($this->campaign_category_id);

        if (! $category) {
            $this->dispatch('toast', type: 'error', message: 'Selected category no longer exists.');
            return;
        }

        try {
            $campaignService->validateRate($category, $this->rate_per_participant);
        } catch (ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: collect($e->errors())->flatten()->first());
            return;
        }

        if ($this->target_participants < $category->min_participants) {
            $this->dispatch('toast', type: 'error', message: "This category requires at least {$category->min_participants} participants.");
            return;
        }

        if ($category->max_participants && $this->target_participants > $category->max_participants) {
            $this->dispatch('toast', type: 'error', message: "This category allows at most {$category->max_participants} participants.");
            return;
        }

        foreach ($this->businessFieldsFor($category) as $field) {
            $value = $this->businessAnswers[$field->id] ?? null;

            if ($field->is_required && empty($value)) {
                $this->dispatch('toast', type: 'error', message: "\"{$field->label}\" is required.");
                return;
            }
        }

        if ($category->supports_post_modes) {
            $this->validate([
                'task_mode' => ['required', 'in:reshare,post_own_content'],
                'platforms' => ['required', 'array', 'min:1'],
                'platforms.*' => ['in:' . implode(',', array_keys(self::PLATFORMS))],
            ]);

            if ($this->task_mode === 'post_own_content') {
                $this->validate([
                    'post_content_text' => ['required', 'string', 'max:2000'],
                    'post_content_media' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm', 'max:20480'],
                ], [
                    'post_content_media.max' => 'That file is too large. Please keep it under 20MB.',
                ]);
            }

            // Reshare needs one source link per selected platform - the
            // business's own post has a different URL on Facebook than on
            // Instagram, so a single link can't stand in for all of them.
            if ($this->task_mode === 'reshare') {
                $rules = [];
                $attributes = [];

                foreach ($this->platforms as $platform) {
                    $key = "platformSourceLinks.{$platform}";
                    $rules[$key] = ['required', 'url'];
                    $attributes[$key] = (self::PLATFORMS[$platform] ?? ucfirst($platform)) . ' post link';
                }

                $this->validate($rules, [], $attributes);
            }
        }

        $platformCount = $category->supports_post_modes ? max(1, count($this->platforms)) : 1;
        $budget = $campaignService->calculateBudget($category, $this->rate_per_participant, $this->target_participants, $platformCount);

        try {
            $campaignService->validateMinimumBudget($budget['total']);
        } catch (ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: collect($e->errors())->flatten()->first());
            return;
        }

        try {
            DB::transaction(function () use ($category, $budget, $campaignService) {
                $steps = collect($this->steps)
                    ->map(fn ($step) => trim((string) $step))
                    ->filter()
                    ->values()
                    ->all();

                $postContentMedia = null;
                if ($category->supports_post_modes && $this->task_mode === 'post_own_content' && $this->post_content_media) {
                    $postContentMedia = $this->storeOptimizedMedia($this->post_content_media);
                }

                $campaign = new Campaign();
                $campaign->forceFill([
                    'user_id' => auth()->id(),
                    'campaign_category_id' => $category->id,
                    'title' => $this->title,
                    'description' => $this->description,
                    'steps' => $steps ?: null,
                    // The bonus for extra platforms (see CampaignService::
                    // calculateBudget()) is baked into effective_rate here,
                    // so it's what actually gets paid out per approved
                    // submission, not just what the business is charged.
                    'rate_per_participant' => $budget['effective_rate'],
                    'target_participants' => $this->target_participants,
                    'total_budget' => $budget['total'],
                    'platform_fee_amount' => $budget['fee'],
                    'status' => 'draft',
                    'allow_admin_edit' => $this->allow_admin_edit,
                    'task_mode' => $category->supports_post_modes ? $this->task_mode : null,
                    'platforms' => $category->supports_post_modes ? $this->platforms : null,
                    'post_content_text' => $this->task_mode === 'post_own_content' ? $this->post_content_text : null,
                    'post_content_media' => $postContentMedia,
                    'platform_source_links' => ($category->supports_post_modes && $this->task_mode === 'reshare')
                        ? array_intersect_key($this->platformSourceLinks, array_flip($this->platforms))
                        : null,
                ])->save();

                foreach ($this->businessFieldsFor($category) as $field) {
                    $value = $this->businessAnswers[$field->id] ?? null;

                    if ($field->type === 'file' && $value) {
                        $value = $value->store('campaign-requirements', 'public');
                    }

                    if ($value === null || $value === '') {
                        continue;
                    }

                    $answer = new CampaignRequirementAnswer();
                    $answer->forceFill([
                        'campaign_id' => $campaign->id,
                        'category_requirement_field_id' => $field->id,
                        'value' => is_string($value) ? $value : (string) $value,
                    ])->save();
                }

                foreach ($this->customFields as $index => $field) {
                    if (empty($field['label'])) {
                        continue;
                    }

                    $customField = new CampaignCustomField();
                    $customField->forceFill([
                        'campaign_id' => $campaign->id,
                        'label' => $field['label'],
                        'field_key' => Str::slug($field['label'], '_'),
                        'type' => $field['type'] ?? 'text',
                        'is_required' => $field['is_required'] ?? false,
                        'sort_order' => $index,
                    ])->save();
                }

                $targeting = new CampaignTargeting();
                $targeting->forceFill([
                    'campaign_id' => $campaign->id,
                    'country_ids' => $this->country_ids,
                    'min_age' => $this->min_age,
                    'max_age' => $this->max_age,
                    'gender' => $this->gender,
                ])->save();

                $campaignService->submit(auth()->user(), $campaign);
            });
        } catch (InsufficientWalletBalanceException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
            return;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Campaign submission failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'exception' => $e,
            ]);
            $this->dispatch('toast', type: 'error', message: 'Something went wrong while submitting your campaign. Please try again.');
            return;
        }

        $this->dispatch('toast', type: 'success', message: 'Campaign submitted for review.');
        $this->redirect(route('dashboard'), navigate: true);
    }

    /**
     * The category's generic business-facing requirement fields, with one
     * adjustment: a generic url-type field never applies to a post-mode
     * category (supports_post_modes), in either mode. Reshare replaces it
     * with the per-platform source links above (platformSourceLinks) -
     * one link can't cover several platforms. Post-your-own-content
     * replaces it with post_content_text/post_content_media - the
     * business gives the caption and flyer directly, there's no existing
     * post to link to at all. Either way the generic link field would
     * just be a leftover, unusable box, so it's hidden as soon as a
     * post-mode category is selected - not conditioned on task_mode,
     * since that's chosen afterwards and it's wrong for every value it
     * could take. Non-url fields (a text note, a file, etc.) are
     * unaffected either way.
     */
    protected function businessFieldsFor(?CampaignCategory $category): \Illuminate\Support\Collection
    {
        $fields = $category?->requirementFields->where('fills_for', 'business') ?? collect();

        if ($category?->supports_post_modes) {
            $fields = $fields->reject(fn ($field) => $field->type === 'url');
        }

        return $fields;
    }

    /**
     * Stores the "post on your own page" flyer/image/video a business
     * uploads. An image gets scaled down and re-encoded as a compressed
     * JPEG (quality 80, capped at 1920px on the long edge) so a business
     * uploading a large photo doesn't quietly balloon our storage; a video
     * is stored as-is for now - see the max:20480 upload rule above for
     * why it can't be huge to begin with. Falls back to storing the
     * original file untouched if Intervention Image isn't installed yet
     * or optimization fails for any reason, so a missing dependency
     * degrades gracefully instead of breaking campaign submission.
     */
    private function storeOptimizedMedia($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);

        if ($isImage && class_exists(\Intervention\Image\ImageManager::class)) {
            try {
                $manager = \Intervention\Image\ImageManager::gd();
                $image = $manager->read($file->getRealPath());
                $image->scaleDown(width: 1920);
                $encoded = $image->toJpeg(quality: 80);

                $path = 'campaign-post-content/' . Str::random(40) . '.jpg';
                Storage::disk('public')->put($path, (string) $encoded);

                return $path;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Campaign post-content image optimization failed, storing original: ' . $e->getMessage());
            }
        }

        return $file->store('campaign-post-content', 'public');
    }

    public function render(CampaignService $campaignService, WalletService $walletService)
    {
        $category = $this->campaign_category_id
            ? CampaignCategory::with('requirementFields')->find($this->campaign_category_id)
            : null;

        $platformCount = ($category && $category->supports_post_modes)
            ? max(1, count($this->platforms))
            : 1;

        $budget = $category
            ? $campaignService->calculateBudget($category, $this->rate_per_participant ?: $category->min_rate, $this->target_participants ?: $category->min_participants, $platformCount)
            : null;

        return view('livewire.campaigns.create', [
            'categories' => CampaignCategory::where('is_active', true)->orderBy('name')->get(),
            'category' => $category,
            'businessFields' => $this->businessFieldsFor($category),
            'countries' => Country::where('is_active', true)->orderBy('name')->get(),
            'budget' => $budget,
            'balance' => $walletService->availableBalance(auth()->user()),
        ])->layout('components.layouts.app', ['title' => 'Create campaign']);
    }
}