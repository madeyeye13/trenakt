<?php

namespace App\Livewire\Admin\Campaigns;

use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'pending_review';
    public string $search = '';

    public ?int $rejectingId = null;
    public string $rejectingTitle = '';
    public string $rejectReason = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function approve(int $id, CampaignService $campaignService): void
    {
        $campaign = Campaign::where('status', 'pending_review')->find($id);

        if (! $campaign) {
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'This campaign is no longer pending review.');
            return;
        }

        try {
            $campaignService->approve($campaign, auth()->user());
        } catch (\Throwable $e) {
            Log::error('Campaign approval failed: ' . $e->getMessage(), [
                'campaign_id' => $id,
                'exception' => $e,
            ]);
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'Could not approve this campaign. Please try again.');
            return;
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Campaign approved and is now live.');
    }

    public function confirmReject(int $id): void
    {
        $campaign = Campaign::where('status', 'pending_review')->find($id);

        if (! $campaign) {
            $this->dispatch('toast', type: 'error', message: 'This campaign is no longer pending review.');
            return;
        }

        $this->rejectingId = $campaign->id;
        $this->rejectingTitle = $campaign->title;
        $this->rejectReason = '';
        $this->dispatch('open-modal', name: 'reject-campaign');
    }

    public function reject(CampaignService $campaignService): void
    {
        $this->validate([
            'rejectReason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [], ['rejectReason' => 'reason']);

        $campaign = Campaign::where('status', 'pending_review')->find($this->rejectingId);

        if (! $campaign) {
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'This campaign is no longer pending review.');
            return;
        }

        try {
            $campaignService->reject($campaign, auth()->user(), $this->rejectReason);
        } catch (\Throwable $e) {
            Log::error('Campaign rejection failed: ' . $e->getMessage(), [
                'campaign_id' => $campaign->id,
                'exception' => $e,
            ]);
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'Could not reject this campaign. Please try again.');
            return;
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Campaign rejected and funds released back to the business.');
        $this->rejectingId = null;
        $this->rejectingTitle = '';
        $this->rejectReason = '';
    }

    public function render()
    {
        $baseQuery = Campaign::query();

        $campaigns = (clone $baseQuery)
            ->with(['category', 'business'])
            ->withCount('submissions')
            ->when($this->status !== 'all', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->search !== '', function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhereHas('business', fn (Builder $b) => $b->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->latest('submitted_at')
            ->paginate(8);

        $counts = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending_review,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        ")->first();

        return view('livewire.admin.campaigns.index', [
            'campaigns' => $campaigns,
            'counts' => [
                'pending_review' => (int) $counts->pending_review,
                'approved' => (int) $counts->approved,
                'rejected' => (int) $counts->rejected,
                'all' => (int) $counts->total,
            ],
        ])->layout('components.layouts.admin', ['title' => 'Campaign review']);
    }
}
