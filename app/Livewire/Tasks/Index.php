<?php

namespace App\Livewire\Tasks;

use App\Models\CampaignSubmission;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'all';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $baseQuery = CampaignSubmission::where('participant_id', auth()->id());

        $submissions = (clone $baseQuery)
            ->with('campaign.category')
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->latest('submitted_at')
            ->paginate(10);

        $counts = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        ")->first();

        return view('livewire.tasks.index', [
            'submissions' => $submissions,
            'counts' => [
                'all' => (int) $counts->total,
                'submitted' => (int) $counts->submitted,
                'approved' => (int) $counts->approved,
                'rejected' => (int) $counts->rejected,
            ],
        ])->layout('components.layouts.app', ['title' => 'My tasks']);
    }
}
