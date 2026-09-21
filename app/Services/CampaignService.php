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

    public function calculateBudget(CampaignCategory $category, float $rate, int $participants): array
    {
        $subtotal = round($rate * $participants, 2);
        $fee = round($subtotal * ($category->platform_fee_percentage / 100), 2);

        return [
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

        $reviewers = User::role(['admin', 'super_admin'])->get();

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