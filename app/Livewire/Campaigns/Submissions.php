<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\CampaignSubmission;
use Livewire\Component;
use Livewire\WithPagination;

class Submissions extends Component
{
    use WithPagination;

    public Campaign $campaign;
    public ?int $selectedSubmissionId = null;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign->load(['category.requirementFields', 'customFields']);
    }

    public function showSubmission(int $submissionId): void
    {
        $this->selectedSubmissionId = CampaignSubmission::query()
            ->where('campaign_id', $this->campaign->id)
            ->whereKey($submissionId)
            ->value('id');

        if ($this->selectedSubmissionId) {
            $this->dispatch('open-modal', name: 'submission-details');
        }
    }

    public function render()
    {
        $submissions = CampaignSubmission::query()
            ->where('campaign_id', $this->campaign->id)
            ->with('participant')
            ->latest('submitted_at')
            ->paginate(10);

        $selectedSubmission = $this->selectedSubmissionId
            ? $submissions->firstWhere('id', $this->selectedSubmissionId)
                ?? CampaignSubmission::with('participant')->find($this->selectedSubmissionId)
            : null;

        return view('livewire.campaigns.submissions', [
            'submissions' => $submissions,
            'selectedSubmission' => $selectedSubmission,
            'participantFields' => $this->campaign->category->requirementFields->where('fills_for', 'participant'),
            'customFields' => $this->campaign->customFields,
        ])->layout('components.layouts.app', ['title' => 'Submissions']);
    }
}
