<?php

namespace App\Livewire\Tasks;

use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\CampaignImpression;
use App\Models\User;
use App\Services\Payments\ActivationFeeService;
use App\Services\TaskService;
use App\Services\Verification\SubmissionVerifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class Discover extends Component
{
    use WithFileUploads;

    public string $search = '';
    public string $category = 'all';

    public ?int $selectedCampaignId = null;
    public bool $started = false;
    public array $answers = [];

    public function viewTask(int $campaignId): void
    {
        $this->selectedCampaignId = $campaignId;
        $this->started = false;
        $this->answers = [];
        $this->resetValidation();

        // Reach counts a participant the moment a campaign is listed to
        // them (see logImpressions() in render()); detail_opened_at is the
        // stronger signal that they actually looked at it. firstOrCreate is
        // just defense in depth in case this is somehow reached without an
        // impression row already existing. Only set once, a reopen doesn't
        // move it.
        $impression = CampaignImpression::firstOrCreate(
            ['campaign_id' => $campaignId, 'participant_id' => auth()->id()],
            ['first_seen_at' => now()],
        );

        if (! $impression->detail_opened_at) {
            $impression->update(['detail_opened_at' => now()]);
        }

        $this->dispatch('open-modal', name: 'task-details');
    }

    public function startTask(): void
    {
        $this->started = true;
    }

    /**
     * Kicks off the Paystack checkout for the one-time activation fee.
     * Only reachable from the "activate to start" prompt the blade shows in
     * place of the normal Start-task button, but TaskService::submit()
     * re-checks this too, so there's nothing to bypass by calling this
     * directly.
     */
    public function activateAccount(ActivationFeeService $activationFee)
    {
        session(['activation_return_to' => route('tasks.discover')]);
        $callbackUrl = route('activation.callback');

        try {
            $init = $activationFee->initialize(auth()->user(), $callbackUrl);
        } catch (\Throwable $e) {
            Log::error('Activation fee gateway initialization failed', [
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
            ]);
            $this->dispatch('toast', type: 'error', message: 'Could not connect to the payment gateway. Please try again.');
            return;
        }

        if (empty($init['authorization_url'])) {
            $this->dispatch('toast', type: 'error', message: 'The payment gateway did not return a payment link. Please try again.');
            return;
        }

        return redirect()->away($init['authorization_url']);
    }

    public function submit(TaskService $tasks)
    {
        $campaign = Campaign::with(['category.requirementFields', 'customFields'])->find($this->selectedCampaignId);

        if (! $campaign) {
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'This task is no longer available.');
            return;
        }

        $participantFields = $campaign->category->requirementFields->where('fills_for', 'participant');
        $rules = [];
        $attributes = [];

        foreach ($participantFields as $field) {
            $key = "answers.{$field->id}";
            $rules[$key] = $this->rulesFor($field);
            $attributes[$key] = $field->label;
        }

        foreach ($campaign->customFields as $field) {
            $key = "answers.{$field->field_key}";
            $rules[$key] = $this->rulesFor($field);
            $attributes[$key] = $field->label;
        }

        $this->validate($rules, [], $attributes);

        $answers = collect($this->answers)->map(function ($value) {
            if (is_object($value) && method_exists($value, 'store')) {
                return $value->store('submission-uploads', 'public');
            }

            return $value;
        })->all();

        try {
            $tasks->submit($campaign, auth()->user(), $answers, app(SubmissionVerifier::class));
        } catch (ValidationException $e) {
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: collect($e->errors())->flatten()->first() ?? 'This task is no longer available to you.');
            return;
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Submitted! We\'ll review it and let you know.');
        $this->reset(['answers', 'started', 'selectedCampaignId']);
    }

    protected function rulesFor($field): array
    {
        $rule = $field->is_required ? ['required'] : ['nullable'];

        return array_merge($rule, match ($field->type) {
            'url' => ['url'],
            'number' => ['numeric'],
            'file' => ['file', 'max:5120'],
            default => ['string', 'max:2000'],
        });
    }

    /**
     * Reach: log that $participant saw each of $campaigns on Discover.
     * insertOrIgnore leans on the unique(campaign_id, participant_id)
     * index to silently skip rows that already exist, so first_seen_at is
     * only ever set once per participant per campaign no matter how many
     * times this page re-renders (search, category filter, pagination).
     */
    protected function logImpressions(Collection $campaigns, User $participant): void
    {
        if ($campaigns->isEmpty()) {
            return;
        }

        $now = now();

        $rows = $campaigns->map(fn (Campaign $campaign) => [
            'campaign_id' => $campaign->id,
            'participant_id' => $participant->id,
            'first_seen_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        CampaignImpression::insertOrIgnore($rows);
    }

    public function render(TaskService $tasks, ActivationFeeService $activationFee)
    {
        $participant = auth()->user();

        $candidates = $tasks->eligibleCampaignsQuery($participant)
            ->when($this->category !== 'all', fn ($q) => $q->where('campaign_category_id', $this->category))
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->with(['category', 'targeting'])
            ->withCount(['submissions as active_submissions_count' => fn ($q) => $q->where('status', '!=', 'rejected')])
            ->latest()
            ->limit(60)
            ->get()
            ->filter(fn (Campaign $campaign) => $tasks->matchesTargeting($campaign->targeting, $participant))
            ->values();

        $this->logImpressions($candidates, $participant);

        $selectedCampaign = $this->selectedCampaignId
            ? Campaign::with(['category.requirementFields', 'customFields', 'requirementAnswers.field'])->find($this->selectedCampaignId)
            : null;

        return view('livewire.tasks.discover', [
            'campaigns' => $candidates,
            'categories' => CampaignCategory::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'selectedCampaign' => $selectedCampaign,
            'participantFields' => $selectedCampaign?->category->requirementFields->where('fills_for', 'participant') ?? collect(),
            'customFields' => $selectedCampaign?->customFields ?? collect(),
            // Plain-text, ordered instructions the business wrote at
            // campaign-creation time (e.g. "Click the link above", "Sign up
            // with your real email") - the primary how-to shown to
            // participants, never hardcoded or guessed at by us.
            'steps' => $selectedCampaign?->steps ?? [],
            // What the business filled in when they created this campaign
            // (an app link to test, a page to visit, etc.) - resources the
            // participant needs, shown separately from the step-by-step guide.
            'businessInfo' => $selectedCampaign?->requirementAnswers ?? collect(),
            'rejectionNote' => $selectedCampaign ? $tasks->latestRejection($selectedCampaign, $participant) : null,
            'profileComplete' => $participant->hasCompleteParticipantProfile(),
            'activationRequired' => $activationFee->isRequiredFor($participant),
            'activationFeeAmount' => $activationFee->amount(),
        ])->layout('components.layouts.app', ['title' => 'Available tasks']);
    }
}
