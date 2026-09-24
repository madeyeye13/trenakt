<?php

namespace App\Livewire\Admin;

use App\Models\ActivationPayment;
use App\Models\Campaign;
use App\Models\CampaignSubmission;
use App\Models\User;
use App\Models\WalletFundingRequest;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * How many months the income-vs-withdrawals chart (and its CSV export)
     * covers - one place so the two never drift apart.
     */
    protected const CHART_MONTHS = 6;

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
            'incomeVsWithdrawals' => $this->incomeVsWithdrawals(),
            'recentActivity' => $this->recentActivity(),
        ])->layout('components.layouts.admin', ['title' => 'Dashboard']);
    }

    /**
     * Monthly money-in vs money-out for the platform, oldest first,
     * zero-filled so the chart never has a gap - same pattern as
     * CampaignAnalyticsService::spendOverTime() on the business side.
     *
     * "Income" is every successful wallet top-up (business funding via
     * Paystack/Flutterwave) plus every successful participant activation
     * fee, grouped by the date the gateway actually verified it, not when
     * the request row was first created. "Withdrawals" is every withdrawal
     * that was actually paid out, grouped by when it was processed.
     */
    protected function incomeVsWithdrawals(): array
    {
        $months = self::CHART_MONTHS;
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $funding = WalletFundingRequest::where('status', 'successful')
            ->where('verified_at', '>=', $start)
            ->selectRaw("to_char(verified_at, 'YYYY-MM') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $activations = ActivationPayment::where('status', 'successful')
            ->where('verified_at', '>=', $start)
            ->selectRaw("to_char(verified_at, 'YYYY-MM') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $withdrawals = WithdrawalRequest::where('status', 'paid')
            ->where('processed_at', '>=', $start)
            ->selectRaw("to_char(processed_at, 'YYYY-MM') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $income = [];
        $paidOut = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M');
            $income[] = (float) (($funding[$key] ?? 0) + ($activations[$key] ?? 0));
            $paidOut[] = (float) ($withdrawals[$key] ?? 0);
        }

        return ['labels' => $labels, 'income' => $income, 'withdrawals' => $paidOut];
    }

    /**
     * CSV of the same months the chart shows, plus a net column - kept as
     * its own export rather than reusing exactly what's on screen so the
     * file is still useful to someone who can't see the chart.
     */
    public function exportCsv()
    {
        $chart = $this->incomeVsWithdrawals();

        return response()->streamDownload(function () use ($chart) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Month', 'Income (NGN)', 'Withdrawals (NGN)', 'Net (NGN)']);

            foreach ($chart['labels'] as $index => $label) {
                $income = $chart['income'][$index];
                $withdrawals = $chart['withdrawals'][$index];

                fputcsv($handle, [
                    $label,
                    number_format($income, 2, '.', ''),
                    number_format($withdrawals, 2, '.', ''),
                    number_format($income - $withdrawals, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, 'trenakt-income-vs-withdrawals-' . now()->format('Y-m-d') . '.csv');
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
