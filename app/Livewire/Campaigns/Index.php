<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'all';
    public string $search = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $baseQuery = Campaign::query()->where('user_id', auth()->id());

        $campaigns = (clone $baseQuery)
            ->with('category')
            ->withCount('submissions')
            ->when($this->status !== 'all', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->search !== '', fn (Builder $query) => $query->where('title', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(8);

        $counts = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending_review,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        ")->first();

        return view('livewire.campaigns.index', [
            'campaigns' => $campaigns,
            'counts' => [
                'all' => (int) $counts->total,
                'pending_review' => (int) $counts->pending_review,
                'approved' => (int) $counts->approved,
                'rejected' => (int) $counts->rejected,
                'completed' => (int) $counts->completed,
            ],
        ])->layout('components.layouts.app', ['title' => 'Campaigns']);
    }
}
