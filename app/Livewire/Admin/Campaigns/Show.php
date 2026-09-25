<?php

namespace App\Livewire\Admin\Campaigns;

use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Show extends Component
{
    public Campaign $campaign;
    public string $rejectReason = '';

    public bool $isEditing = false;
    public string $editTitle = '';
    public string $editDescription = '';
    public array $editSteps = [];

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign->load([
            'category.requirementFields',
            'business',
            'reviewer',
            'adminEditor',
            'targeting',
            'requirementAnswers.field',
            'customFields',
        ]);

        $this->editTitle = $this->campaign->title;
        $this->editDescription = $this->campaign->description;
        $this->editSteps = $this->campaign->steps ?? [];
    }

    public function startEditing(): void
    {
        if (! $this->campaign->allow_admin_edit || $this->campaign->status !== 'pending_review') {
            return;
        }

        $this->editTitle = $this->campaign->title;
        $this->editDescription = $this->campaign->description;
        $this->editSteps = $this->campaign->steps ?? [];
        $this->isEditing = true;
    }

    public function cancelEditing(): void
    {
        $this->isEditing = false;
    }

    public function addEditStep(): void
    {
        $this->editSteps[] = '';
    }

    public function removeEditStep(int $index): void
    {
        unset($this->editSteps[$index]);
        $this->editSteps = array_values($this->editSteps);
    }

    /**
     * Saves an admin's edits to the campaign's wording. Only reachable when
     * the business opted in (allow_admin_edit) and the campaign is still
     * pending_review - see the migration that adds these columns for why.
     * The FIRST save on a campaign snapshots the pre-edit title/description/
     * steps into original_content so the business can always see what they
     * originally wrote, even if it's edited again later in the same review.
     */
    public function saveEdit(): void
    {
        if (! $this->campaign->allow_admin_edit) {
            $this->dispatch('toast', type: 'error', message: 'This business did not allow edits to this campaign.');
            return;
        }

        if ($this->campaign->status !== 'pending_review') {
            $this->dispatch('toast', type: 'error', message: 'This campaign is no longer pending review.');
            return;
        }

        $this->validate([
            'editTitle' => ['required', 'string', 'max:255'],
            'editDescription' => ['required', 'string'],
            'editSteps.*' => ['nullable', 'string', 'max:500'],
        ]);

        $steps = collect($this->editSteps)
            ->map(fn ($step) => trim((string) $step))
            ->filter()
            ->values()
            ->all();

        $updates = [
            'title' => $this->editTitle,
            'description' => $this->editDescription,
            'steps' => $steps ?: null,
            'admin_edited_at' => now(),
            'admin_edited_by' => auth()->id(),
        ];

        if (is_null($this->campaign->original_content)) {
            $updates['original_content'] = [
                'title' => $this->campaign->title,
                'description' => $this->campaign->description,
                'steps' => $this->campaign->steps,
            ];
        }

        $this->campaign->forceFill($updates)->save();
        $this->campaign->refresh()->load('adminEditor');

        $this->isEditing = false;
        $this->dispatch('toast', type: 'success', message: 'Changes saved.');
    }

    public function approve(CampaignService $campaignService): void
    {
        if ($this->campaign->status !== 'pending_review') {
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'This campaign is no longer pending review.');
            return;
        }

        try {
            $campaignService->approve($this->campaign, auth()->user());
        } catch (\Throwable $e) {
            Log::error('Campaign approval failed: ' . $e->getMessage(), [
                'campaign_id' => $this->campaign->id,
                'exception' => $e,
            ]);
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'Could not approve this campaign. Please try again.');
            return;
        }

        $this->campaign->refresh();
        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Campaign approved and is now live.');
    }

    public function reject(CampaignService $campaignService): void
    {
        $this->validate([
            'rejectReason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [], ['rejectReason' => 'reason']);

        if ($this->campaign->status !== 'pending_review') {
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'This campaign is no longer pending review.');
            return;
        }

        try {
            $campaignService->reject($this->campaign, auth()->user(), $this->rejectReason);
        } catch (\Throwable $e) {
            Log::error('Campaign rejection failed: ' . $e->getMessage(), [
                'campaign_id' => $this->campaign->id,
                'exception' => $e,
            ]);
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'Could not reject this campaign. Please try again.');
            return;
        }

        $this->campaign->refresh();
        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Campaign rejected and funds released back to the business.');
        $this->rejectReason = '';
    }

    public function render()
    {
        return view('livewire.admin.campaigns.show', [
            'subtotal' => (float) $this->campaign->total_budget - (float) $this->campaign->platform_fee_amount,
        ])->layout('components.layouts.admin', ['title' => $this->campaign->title]);
    }
}
