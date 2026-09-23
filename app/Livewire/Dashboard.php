<?php

namespace App\Livewire;

use App\Models\Campaign;
use App\Models\CampaignSubmission;
use App\Models\WalletTransaction;
use App\Services\CampaignAnalyticsService;
use App\Services\ParticipantWalletService;
use App\Services\Payments\WithdrawalService;
use App\Services\TaskService;
use App\Services\WalletService;
use Livewire\Component;

class Dashboard extends Component
{
    public function mount(): void
    {
        if (session()->has('toast')) {
            $toast = session('toast');
            $this->dispatch('toast', type: $toast['type'], message: $toast['message']);
        }
    }

    public function render(
        WalletService $businessWallet,
        ParticipantWalletService $participantWallet,
        TaskService $tasks,
        WithdrawalService $withdrawals,
        CampaignAnalyticsService $analytics,
    ) {
        $user = auth()->user();

        return $user->activeMode() === 'business'
            ? $this->renderBusiness($user, $businessWallet, $analytics)
            : $this->renderParticipant($user, $participantWallet, $tasks, $withdrawals);
    }

    protected function renderBusiness($user, WalletService $wallet, CampaignAnalyticsService $analytics)
    {
        $baseQuery = Campaign::where('user_id', $user->id);

        $counts = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending_review,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        ")->first();

        $totalSpent = WalletTransaction::where('user_id', $user->id)
            ->where('wallet_type', 'business')
            ->where('type', 'campaign_spend')
            ->sum('amount');

        return view('livewire.dashboard', [
            'mode' => 'business',
            'availableBalance' => $wallet->availableBalance($user),
            'campaignCounts' => [
                'total' => (int) $counts->total,
                'pending_review' => (int) $counts->pending_review,
                'approved' => (int) $counts->approved,
                'completed' => (int) $counts->completed,
            ],
            'totalSpent' => (float) $totalSpent,
            'spendOverTime' => $analytics->spendOverTime($user),
            'recentCampaigns' => (clone $baseQuery)->with('category')->withCount('submissions')->latest()->limit(5)->get(),
        ])->layout('components.layouts.app', ['title' => 'Dashboard']);
    }

    protected function renderParticipant($user, ParticipantWalletService $wallet, TaskService $tasks, WithdrawalService $withdrawals)
    {
        $baseQuery = CampaignSubmission::where('participant_id', $user->id);

        $counts = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
        ")->first();

        $profileComplete = $user->hasCompleteParticipantProfile();

        return view('livewire.dashboard', [
            'mode' => 'participant',
            'availableBalance' => $wallet->availableBalance($user),
            'submissionCounts' => [
                'total' => (int) $counts->total,
                'submitted' => (int) $counts->submitted,
                'approved' => (int) $counts->approved,
            ],
            // A rough count (SQL-only: live, room, no active submission) - not
            // filtered by age/country targeting, since this is just a dashboard
            // teaser. Discover is the precise, filtered source of truth.
            'availableTasksCount' => $profileComplete ? $tasks->eligibleCampaignsQuery($user)->count() : 0,
            'recentSubmissions' => (clone $baseQuery)->with('campaign.category')->latest('submitted_at')->limit(5)->get(),
            'profileComplete' => $profileComplete,
            'canWithdraw' => $user->isEligibleForWithdrawal() && $withdrawals->isPayoutWindowOpen(),
        ])->layout('components.layouts.app', ['title' => 'Dashboard']);
    }
}
