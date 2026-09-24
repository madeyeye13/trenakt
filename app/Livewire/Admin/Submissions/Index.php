<?php

namespace App\Livewire\Admin\Submissions;

use App\Models\CampaignSubmission;
use App\Models\RejectionReason;
use App\Services\ParticipantWalletService;
use App\Services\TaskService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'submitted';
    public string $search = '';

    public ?int $selectedSubmissionId = null;
    public string $rejectNote = '';
    public ?int $selectedReasonId = null;
    public string $revokeReason = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function showSubmission(int $submissionId): void
    {
        $this->selectedSubmissionId = $submissionId;
        $this->rejectNote = '';
        $this->selectedReasonId = null;
        $this->revokeReason = '';
        $this->dispatch('open-modal', name: 'submission-details');
    }

    public function approve(TaskService $tasks): void
    {
        $submission = CampaignSubmission::find($this->selectedSubmissionId);

        if (! $submission) {
            $this->dispatch('close-modal');
            return;
        }

        try {
            $tasks->approve($submission, auth()->user());
        } catch (\Throwable $e) {
            Log::error('Submission approval failed: ' . $e->getMessage(), ['submission_id' => $submission->id]);
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'Could not approve this submission. Please try again.');
            return;
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Submission approved and reward paid.');
    }

    public function reject(TaskService $tasks): void
    {
        $this->validate([
            'selectedReasonId' => ['required', 'exists:rejection_reasons,id'],
        ], [], ['selectedReasonId' => 'reason']);

        $submission = CampaignSubmission::find($this->selectedSubmissionId);
        $reason = RejectionReason::find($this->selectedReasonId);

        if (! $submission || ! $reason) {
            $this->dispatch('close-modal');
            return;
        }

        try {
            $tasks->reject($submission, auth()->user(), $reason, $this->rejectNote ?: null);
        } catch (\Throwable $e) {
            Log::error('Submission rejection failed: ' . $e->getMessage(), ['submission_id' => $submission->id]);
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'Could not reject this submission. Please try again.');
            return;
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Submission rejected. The participant can try again.');
        $this->rejectNote = '';
        $this->selectedReasonId = null;
    }

    public function revoke(TaskService $tasks): void
    {
        $this->validate([
            'revokeReason' => ['required', 'string', 'max:1000'],
        ], [], ['revokeReason' => 'reason']);

        $submission = CampaignSubmission::find($this->selectedSubmissionId);

        if (! $submission) {
            $this->dispatch('close-modal');
            return;
        }

        try {
            $tasks->revokeReward($submission, auth()->user(), $this->revokeReason);
        } catch (\Throwable $e) {
            Log::error('Reward revocation failed: ' . $e->getMessage(), ['submission_id' => $submission->id]);
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'Could not revoke this reward. Please try again.');
            return;
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Reward revoked and removed from the participant\'s earnings.');
        $this->revokeReason = '';
    }

    public function render()
    {
        $baseQuery = CampaignSubmission::query();

        $submissions = (clone $baseQuery)
            ->with(['participant', 'campaign'])
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q2) use ($term) {
                    $q2->whereHas('participant', fn ($p) => $p->where('name', 'like', $term))
                        ->orWhereHas('campaign', fn ($c) => $c->where('title', 'like', $term));
                });
            })
            ->latest('submitted_at')
            ->paginate(10);

        $counts = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        ")->first();

        $selectedSubmission = $this->selectedSubmissionId
            ? CampaignSubmission::with(['participant', 'campaign.category.requirementFields', 'campaign.customFields', 'latestVerification'])->find($this->selectedSubmissionId)
            : null;

        // Only needed to show a heads-up in the revoke confirmation (see the
        // blade) when the participant's current balance is already below
        // the reward amount - a sign some or all of it was likely already
        // withdrawn. Not computed unless there's a selected, approved,
        // not-yet-revoked submission to actually show it for.
        $participantBalance = ($selectedSubmission && $selectedSubmission->status === 'approved' && ! $selectedSubmission->reward_revoked_at)
            ? app(ParticipantWalletService::class)->availableBalance($selectedSubmission->participant)
            : null;

        return view('livewire.admin.submissions.index', [
            'submissions' => $submissions,
            'counts' => [
                'all' => (int) $counts->total,
                'submitted' => (int) $counts->submitted,
                'approved' => (int) $counts->approved,
                'rejected' => (int) $counts->rejected,
            ],
            'selectedSubmission' => $selectedSubmission,
            'participantFields' => $selectedSubmission?->campaign->category->requirementFields->where('fills_for', 'participant') ?? collect(),
            'customFields' => $selectedSubmission?->campaign->customFields ?? collect(),
            'reasons' => RejectionReason::active()->get(),
            'participantBalance' => $participantBalance,
        ])->layout('components.layouts.admin', ['title' => 'Submissions']);
    }
}
