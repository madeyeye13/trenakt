<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use App\Models\CampaignSubmission;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $withdrawalTotals = WithdrawalRequest::where('status', 'pending')
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(amount), 0) as amount')
            ->first();

        $totalSpent = WalletTransaction::where('wallet_type', 'business')
            ->where('type', 'campaign_spend')
            ->sum('amount');

        $totalRewardsPaid = WalletTransaction::where('wallet_type', 'participant')
            ->where('type', 'participant_reward_earned')
            ->sum('amount');

        return view('livewire.admin.dashboard', [
            'pendingCampaigns' => Campaign::where('status', 'pending_review')->count(),
            'pendingSubmissions' => CampaignSubmission::where('status', 'submitted')->count(),
            'pendingWithdrawalsCount' => (int) $withdrawalTotals->total,
            'pendingWithdrawalsAmount' => (float) $withdrawalTotals->amount,
            'totalBusinesses' => User::role('business')->count(),
            'totalParticipants' => User::role('participant')->count(),
            'totalSpent' => (float) $totalSpent,
            'totalRewardsPaid' => (float) $totalRewardsPaid,
            'recentActivity' => $this->recentActivity(),
        ])->layout('components.layouts.admin', ['title' => 'Dashboard']);
    }

    /**
     * A single, time-sorted feed of everything currently waiting on an
     * admin: pending campaign reviews, pending submissions, and pending
     * withdrawals - merged so the dashboard shows one "needs attention"
     * list instead of three separate ones.
     */
    protected function recentActivity(): Collection
    {
        $campaigns = Campaign::where('status', 'pending_review')
            ->with('business')
            ->latest('submitted_at')
            ->limit(8)
            ->get()
            ->map(fn (Campaign $campaign) => [
                'icon' => 'briefcase',
                'label' => ($campaign->business->name ?? 'A business') . ' submitted "' . $campaign->title . '" for review',
                'url' => route('admin.campaigns.show', $campaign),
                'at' => $campaign->submitted_at,
            ]);

        $submissions = CampaignSubmission::where('status', 'submitted')
            ->with(['participant', 'campaign'])
            ->latest('submitted_at')
            ->limit(8)
            ->get()
            ->map(fn (CampaignSubmission $submission) => [
                'icon' => 'clock',
                'label' => ($submission->participant->name ?? 'A participant') . ' submitted "' . ($submission->campaign->title ?? 'a deleted campaign') . '"',
                'url' => route('admin.submissions.index'),
                'at' => $submission->submitted_at,
            ]);

        $withdrawals = WithdrawalRequest::where('status', 'pending')
            ->with('user')
            ->latest('requested_at')
            ->limit(8)
            ->get()
            ->map(fn (WithdrawalRequest $withdrawal) => [
                'icon' => 'bank',
                'label' => ($withdrawal->user->name ?? 'A participant') . ' requested a ₦' . number_format($withdrawal->amount, 2) . ' withdrawal',
                'url' => route('admin.withdrawals.index'),
                'at' => $withdrawal->requested_at,
            ]);

        return $campaigns->concat($submissions)->concat($withdrawals)
            ->sortByDesc('at')
            ->take(8)
            ->values();
    }
}
