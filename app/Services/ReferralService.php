<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Notifications\ReferralRewardEarned;

/**
 * Referral codes/links and the reward paid when a referred participant
 * completes their one-time activation payment. The referral system only
 * has anything to reward with once the activation fee is switched on (see
 * ActivationFeeService) - registration only captures referred_by_user_id
 * while that's true, so a code arriving via ?ref= or typed in is otherwise
 * just ignored rather than stored.
 */
class ReferralService
{
    public function __construct(protected ParticipantWalletService $wallet)
    {
    }

    public function rewardAmount(): float
    {
        return (float) Setting::get('referral_reward_amount', 0);
    }

    /**
     * Resolves a code typed or auto-filled at registration to the
     * participant who owns it. Returns null for an unknown, empty, or
     * malformed code so a bad code never blocks registration - it's simply
     * ignored and no referrer gets attached.
     */
    public function resolve(?string $code): ?User
    {
        $code = $code ? strtoupper(trim($code)) : null;

        return $code !== '' && $code !== null ? User::where('referral_code', $code)->first() : null;
    }

    public function linkFor(User $user): string
    {
        return route('register', ['ref' => $user->referralCode()]);
    }

    /**
     * Called by ActivationFeeService once a participant's activation
     * payment has been verified successful. Pays their referrer, if they
     * have one, the currently configured reward. A no-op if they weren't
     * referred, or if the reward amount is currently zero.
     */
    public function rewardReferrerFor(User $paidParticipant): void
    {
        $referrer = $paidParticipant->referredBy;
        $amount = $this->rewardAmount();

        if (! $referrer || $amount <= 0) {
            return;
        }

        $this->wallet->creditReferralReward($referrer, $amount, $paidParticipant);

        $referrer->notify(new ReferralRewardEarned($paidParticipant, $amount));
    }
}
