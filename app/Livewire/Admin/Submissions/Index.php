<?php

namespace App\Livewire\Admin\Submissions;

use App\Models\CampaignSubmission;
use App\Models\RejectionReason;
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
            ? CampaignSubmission::with(['participant', 'campaign.category.requirementFields', 'campaign.customFields'])->find($this->selectedSubmissionId)
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
        ])->layout('components.layouts.admin', ['title' => 'Submissions']);
    }
}
