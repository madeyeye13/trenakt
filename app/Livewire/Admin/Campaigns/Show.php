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

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign->load([
            'category.requirementFields',
            'business',
            'reviewer',
            'targeting',
            'requirementAnswers.field',
            'customFields',
        ]);
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
