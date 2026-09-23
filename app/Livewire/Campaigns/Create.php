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

        foreach ($category->requirementFields->where('fills_for', 'business') as $field) {
            $value = $this->businessAnswers[$field->id] ?? null;

            if ($field->is_required && empty($value)) {
                $this->dispatch('toast', type: 'error', message: "\"{$field->label}\" is required.");
                return;
            }
        }

        $budget = $campaignService->calculateBudget($category, $this->rate_per_participant, $this->target_participants);

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

                $campaign = new Campaign();
                $campaign->forceFill([
                    'user_id' => auth()->id(),
                    'campaign_category_id' => $category->id,
                    'title' => $this->title,
                    'description' => $this->description,
                    'steps' => $steps ?: null,
                    'rate_per_participant' => $this->rate_per_participant,
                    'target_participants' => $this->target_participants,
                    'total_budget' => $budget['total'],
                    'platform_fee_amount' => $budget['fee'],
                    'status' => 'draft',
                ])->save();

                foreach ($category->requirementFields->where('fills_for', 'business') as $field) {
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

    public function render(CampaignService $campaignService, WalletService $walletService)
    {
        $category = $this->campaign_category_id
            ? CampaignCategory::with('requirementFields')->find($this->campaign_category_id)
            : null;

        $budget = $category
            ? $campaignService->calculateBudget($category, $this->rate_per_participant ?: $category->min_rate, $this->target_participants ?: $category->min_participants)
            : null;

        return view('livewire.campaigns.create', [
            'categories' => CampaignCategory::where('is_active', true)->orderBy('name')->get(),
            'category' => $category,
            'countries' => Country::where('is_active', true)->orderBy('name')->get(),
            'budget' => $budget,
            'balance' => $walletService->availableBalance(auth()->user()),
        ])->layout('components.layouts.app', ['title' => 'Create campaign']);
    }
}