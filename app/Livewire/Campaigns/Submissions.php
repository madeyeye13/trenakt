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
    public string $status = 'all';

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign->load(['category.requirementFields', 'customFields']);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function showSubmission(int $submissionId): void
    {
        $this->selectedSubmissionId = $this->visibleQuery()
            ->whereKey($submissionId)
            ->value('id');

        if ($this->selectedSubmissionId) {
            $this->dispatch('open-modal', name: 'submission-details');
        }
    }

    /**
     * A business only ever sees a submission once its reward has actually
     * been paid (or was never subject to monitoring in the first place) -
     * one still on hold, or one monitoring ended up forfeiting, simply
     * doesn't exist from their side until/unless it's released. See
     * SubmissionMonitoringService for what sets monitoring_status.
     */
    protected function visibleQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return CampaignSubmission::query()
            ->where('campaign_id', $this->campaign->id)
            ->where(function ($query) {
                $query->whereNull('monitoring_status')
                    ->orWhere('monitoring_status', 'released');
            });
    }

    public function render()
    {
        $baseQuery = $this->visibleQuery();

        $submissions = (clone $baseQuery)
            ->with('participant')
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->latest('submitted_at')
            ->paginate(10);

        $counts = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        ")->first();

        $selectedSubmission = $this->selectedSubmissionId
            ? $submissions->firstWhere('id', $this->selectedSubmissionId)
                ?? CampaignSubmission::with('participant')->find($this->selectedSubmissionId)
            : null;

        $participantFields = $this->campaign->category->requirementFields->where('fills_for', 'participant');

        // A post-mode campaign (Reshare or Post-your-own-content) collects
        // one proof link per selected platform instead of the category's
        // generic url field - see Discover::submit(). That field is never
        // filled in for these campaigns, so it's skipped here too rather
        // than showing an empty "No answer provided" box.
        if ($this->campaign->task_mode) {
            $participantFields = $participantFields->reject(fn ($field) => $field->type === 'url');
        }

        return view('livewire.campaigns.submissions', [
            'submissions' => $submissions,
            'counts' => [
                'all' => (int) $counts->total,
                'submitted' => (int) $counts->submitted,
                'approved' => (int) $counts->approved,
                'rejected' => (int) $counts->rejected,
            ],
            'selectedSubmission' => $selectedSubmission,
            'participantFields' => $participantFields,
            'customFields' => $this->campaign->customFields,
            // platform => label for the per-platform links a post-mode
            // campaign collected, keyed the same as answers.platform_links.
            'platformLinks' => $this->campaign->task_mode
                ? collect($this->campaign->platforms ?? [])->mapWithKeys(
                    fn ($platform) => [$platform => \App\Livewire\Campaigns\Create::PLATFORMS[$platform] ?? ucfirst($platform)]
                )
                : collect(),
        ])->layout('components.layouts.app', ['title' => 'Submissions']);
    }
}
