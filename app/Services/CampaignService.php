<?php

namespace App\Services;

use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\Campaign;
use App\Models\CampaignCategory;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignService
{
    public function __construct(protected WalletService $wallet)
    {
    }

    /**
     * $platformCount only matters for a category with supports_post_modes
     * on (see its migration) - every other category always calls this
     * with the default of 1, so platform_bonus is always 0 and this
     * behaves exactly as it did before platform selection existed.
     *
     * The bonus for platformCount > 1 is (platformCount - 1) *
     * category.platform_bonus_amount, added straight onto the rate the
     * business picked. The resulting effective_rate is what actually gets
     * stored as the campaign's rate_per_participant and paid out per
     * approved submission - the bonus raises what participants earn, not
     * just what the business is charged.
     */
    public function calculateBudget(CampaignCategory $category, float $rate, int $participants, int $platformCount = 1): array
    {
        $platformBonus = $platformCount > 1
            ? round(($platformCount - 1) * (float) $category->platform_bonus_amount, 2)
            : 0.0;

        $effectiveRate = round($rate + $platformBonus, 2);
        $subtotal = round($effectiveRate * $participants, 2);
        $fee = round($subtotal * ($category->platform_fee_percentage / 100), 2);

        return [
            'base_rate' => round($rate, 2),
            'platform_bonus' => $platformBonus,
            'effective_rate' => $effectiveRate,
            'subtotal' => $subtotal,
            'fee' => $fee,
            'total' => round($subtotal + $fee, 2),
        ];
    }

    public function validateRate(CampaignCategory $category, float $rate): void
    {
        if ($rate < $category->min_rate || $rate > $category->max_rate) {
            throw ValidationException::withMessages([
                'rate_per_participant' => "Rate must be between {$category->min_rate} and {$category->max_rate} for this category.",
            ]);
        }
    }

    public function validateMinimumBudget(float $total): void
    {
        $minimum = (float) Setting::get('minimum_campaign_budget', 5000);

        if ($total < $minimum) {
            throw ValidationException::withMessages([
                'total_budget' => "Campaign budget must be at least {$minimum}.",
            ]);
        }
    }

    public function submit(User $business, Campaign $campaign): void
    {
        DB::transaction(function () use ($business, $campaign) {
            $this->wallet->reserve($business, (float) $campaign->total_budget, $campaign);

            $campaign->forceFill([
                'status' => 'pending_review',
                'submitted_at' => now(),
            ])->save();
        });

        $business->notify(new \App\Notifications\CampaignSubmitted($campaign));

        // Permission-driven for the same reason as TaskService::submit():
        // whichever roles a super admin has given manage-campaigns to are
        // the ones that hear about it, not a fixed role-name list.
        $reviewers = User::permission('manage-campaigns')->get();

        if ($reviewers->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($reviewers, new \App\Notifications\CampaignPendingReview($campaign));
        }
    }

        public function approve(Campaign $campaign, User $admin): void
    {
        DB::transaction(function () use ($campaign, $admin) {
            $this->wallet->spend(
                $campaign->business,
                (float) $campaign->total_budget,
                (float) $campaign->platform_fee_amount,
                $campaign
            );

            $campaign->forceFill([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
            ])->save();
        });

        $campaign->business->notify(new \App\Notifications\CampaignApproved($campaign));
    }

    public function reject(Campaign $campaign, User $admin, string $reason): void
    {
        DB::transaction(function () use ($campaign, $admin, $reason) {
            $this->wallet->release($campaign->business, (float) $campaign->total_budget, $campaign);

            $campaign->forceFill([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'reviewed_at' => now(),
                'reviewed_by' => $admin->id,
            ])->save();
        });

        $campaign->business->notify(new \App\Notifications\CampaignRejected($campaign, $reason));
    }
}