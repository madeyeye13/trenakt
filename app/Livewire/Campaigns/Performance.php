<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Services\CampaignAnalyticsService;
use Livewire\Component;

class Performance extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        abort_unless($campaign->user_id === auth()->id(), 403);

        $this->campaign = $campaign->load('category');
    }

    public function render(CampaignAnalyticsService $analytics)
    {
        return view('livewire.campaigns.performance', [
            'summary' => $analytics->performanceSummary($this->campaign),
        ])->layout('components.layouts.app', ['title' => 'Performance']);
    }
}
