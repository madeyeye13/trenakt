<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignImpression;
use App\Models\CampaignSubmission;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Business-facing reporting: reach, participants, completion, and spend,
 * for a single campaign (the performance page) and across a business's
 * whole campaign history (the reports page). Kept separate from
 * CampaignService, which is about the campaign lifecycle itself (submit,
 * approve, reject), not reporting on it.
 */
class CampaignAnalyticsService
{
    public function __construct(protected TaskService $tasks)
    {
    }

    /**
     * How many active participants currently match this campaign's
     * targeting (age/country) - a live estimate of the audience it could
     * reach, computed with the same rules Discover itself uses. Country
     * filtering happens in SQL since it's an indexed column; age filtering
     * runs in PHP afterward since age is derived from date_of_birth, exactly
     * like TaskService::eligibleCampaignsQuery() does per-campaign.
     */
    public function reachEstimate(Campaign $campaign): int
    {
        $targeting = $campaign->targeting;

        $participants = User::role('participant')
            ->when(
                $targeting && ! empty($targeting->country_ids),
                fn ($q) => $q->whereIn('country_id', $targeting->country_ids)
            )
            ->get();

        return $participants
            ->filter(fn (User $participant) => $this->tasks->matchesTargeting($targeting, $participant))
            ->count();
    }

    /**
     * Real, tracked engagement from campaign_impressions: distinct
     * participants who had this campaign listed on Discover (views) and how
     * many of those went on to open its task details, a stronger signal
     * than just being shown it.
     */
    public function engagement(Campaign $campaign): array
    {
        $impressions = CampaignImpression::where('campaign_id', $campaign->id);

        return [
            'reach_estimate' => $this->reachEstimate($campaign),
            'views' => (clone $impressions)->count(),
            'detail_opens' => (clone $impressions)->whereNotNull('detail_opened_at')->count(),
        ];
    }

    public function submissionCounts(Campaign $campaign): array
    {
        $counts = CampaignSubmission::where('campaign_id', $campaign->id)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        ")->first();

        return [
            'total' => (int) $counts->total,
            'submitted' => (int) $counts->submitted,
            'approved' => (int) $counts->approved,
            'rejected' => (int) $counts->rejected,
        ];
    }

    /**
     * Fill rate (how much of the target this campaign has filled with
     * approved completions) and approval rate (how much of what came in
     * actually passed review), plus how long reviews are taking on average.
     * Rates are 0 rather than a divide-by-zero when there's nothing to
     * measure yet.
     */
    public function completionRates(Campaign $campaign): array
    {
        $counts = $this->submissionCounts($campaign);

        $avgReviewHours = CampaignSubmission::where('campaign_id', $campaign->id)
            ->whereNotNull('reviewed_at')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (reviewed_at - submitted_at)) / 3600) as avg_hours')
            ->value('avg_hours');

        return [
            'fill_rate' => $campaign->target_participants > 0
                ? round(($counts['approved'] / $campaign->target_participants) * 100, 1)
                : 0.0,
            'approval_rate' => $counts['total'] > 0
                ? round(($counts['approved'] / $counts['total']) * 100, 1)
                : 0.0,
            'avg_review_hours' => $avgReviewHours !== null ? round((float) $avgReviewHours, 1) : null,
        ];
    }

    /**
     * Submissions on this campaign broken down by participant country, for
     * a "who actually showed up" picture. Sorted by count and capped so a
     * long tail of one-off countries doesn't crowd the display.
     */
    public function participantsByCountry(Campaign $campaign, int $limit = 6): Collection
    {
        return CampaignSubmission::where('campaign_id', $campaign->id)
            ->with('participant.country')
            ->get()
            ->groupBy(fn (CampaignSubmission $submission) => $submission->participant->country?->name ?? 'Unknown')
            ->map->count()
            ->sortDesc()
            ->take($limit);
    }

    /**
     * This campaign's money picture. The business's own budget (subtotal +
     * platform fee) is spent in one lump sum when the campaign is approved
     * and goes live (see WalletService::spend()), not per submission - so
     * "spent" here is that one-time event, while "payout" tracks the
     * participant-reward pool actually being paid out as submissions get
     * approved, which is the number that moves week to week.
     */
    public function financials(Campaign $campaign): array
    {
        $counts = $this->submissionCounts($campaign);

        $payoutPool = (float) $campaign->rate_per_participant * $campaign->target_participants;
        $payoutToDate = (float) $campaign->rate_per_participant * $counts['approved'];
        $spent = in_array($campaign->status, ['approved', 'completed'], true) ? (float) $campaign->total_budget : 0.0;

        return [
            'total_budget' => (float) $campaign->total_budget,
            'platform_fee' => (float) $campaign->platform_fee_amount,
            'spent' => $spent,
            'payout_pool' => $payoutPool,
            'payout_to_date' => $payoutToDate,
            'payout_remaining' => max($payoutPool - $payoutToDate, 0.0),
            'cost_per_submission' => $counts['total'] > 0 ? round($spent / $counts['total'], 2) : null,
            'cost_per_approval' => $counts['approved'] > 0 ? round($spent / $counts['approved'], 2) : null,
        ];
    }

    /**
     * Everything the per-campaign performance page needs, bundled into one
     * call so the Livewire component's render() stays thin.
     */
    public function performanceSummary(Campaign $campaign): array
    {
        return [
            'engagement' => $this->engagement($campaign),
            'submissions' => $this->submissionCounts($campaign),
            'completion' => $this->completionRates($campaign),
            'financials' => $this->financials($campaign),
            'participantsByCountry' => $this->participantsByCountry($campaign),
        ];
    }

    /**
     * Monthly campaign_spend totals for this business over the last
     * $months months, oldest first, zero-filled for months with no spend so
     * the chart never has a gap.
     */
    public function spendOverTime(User $business, int $months = 6): array
    {
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        $rows = WalletTransaction::where('user_id', $business->id)
            ->where('wallet_type', 'business')
            ->where('type', 'campaign_spend')
            ->where('created_at', '>=', $start)
            ->selectRaw("to_char(created_at, 'YYYY-MM') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $data = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->format('M');
            $data[] = (float) ($rows[$key] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Lifetime spend grouped by campaign category, for the business-wide
     * reports page. Only counts campaigns that actually went live (spend
     * only happens once a campaign is approved, see financials() above).
     */
    public function spendByCategory(User $business): Collection
    {
        return Campaign::where('user_id', $business->id)
            ->whereIn('status', ['approved', 'completed'])
            ->with('category')
            ->get()
            ->groupBy(fn (Campaign $campaign) => $campaign->category->name ?? 'Uncategorized')
            ->map(fn (Collection $campaigns) => (float) $campaigns->sum('total_budget'))
            ->sortDesc();
    }

    /**
     * One row per campaign with the performance columns the reports table
     * (and its CSV export) show, newest first.
     */
    public function campaignsReport(User $business): Collection
    {
        return Campaign::where('user_id', $business->id)
            ->with('category')
            ->withCount('submissions')
            ->withCount(['submissions as approved_count' => fn ($q) => $q->where('status', 'approved')])
            ->latest()
            ->get()
            ->map(function (Campaign $campaign) {
                $completion = $this->completionRates($campaign);
                $financials = $this->financials($campaign);

                return [
                    'id' => $campaign->id,
                    'title' => $campaign->title,
                    'category' => $campaign->category->name ?? 'Uncategorized',
                    'status' => $campaign->status,
                    'submissions' => $campaign->submissions_count,
                    'approved' => $campaign->approved_count,
                    'fill_rate' => $completion['fill_rate'],
                    'spent' => $financials['spent'],
                    'created_at' => $campaign->created_at,
                ];
            });
    }
}
