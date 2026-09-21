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

        return view('livewire.campaigns.index', [
            'campaigns' => $campaigns,
            'counts' => [
                'all' => (clone $baseQuery)->count(),
                'pending_review' => (clone $baseQuery)->where('status', 'pending_review')->count(),
                'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
                'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
                'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            ],
        ])->layout('components.layouts.app', ['title' => 'Campaigns']);
    }
}
