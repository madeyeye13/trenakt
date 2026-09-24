<?php

namespace App\Services;

use App\Models\ParticipantWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Mirrors WalletService, but for the participant ("earning") side: no
 * funding action exists here on purpose - a participant wallet is only ever
 * credited by TaskService::approve() and only ever debited by a successful
 * withdrawal.
 */
class ParticipantWalletService
{
    public function walletFor(User $user): ParticipantWallet
    {
        return ParticipantWallet::firstOrCreate(['user_id' => $user->id]);
    }

    public function availableBalance(User $user): float
    {
        $wallet = $this->walletFor($user);

        $earned = WalletTransaction::where('user_id', $user->id)
            ->where('wallet_type', 'participant')
            ->whereIn('type', ['participant_reward_earned', 'referral_reward_earned'])
            ->sum('amount');

        $withdrawn = WalletTransaction::where('user_id', $user->id)
            ->where('wallet_type', 'participant')
            ->where('type', 'withdrawal_successful')
            ->sum('amount');

        $adjustments = WalletTransaction::where('user_id', $user->id)
            ->where('wallet_type', 'participant')
            ->where('type', 'manual_admin_adjustment')
            ->sum('amount');

        return (float) ($earned + $adjustments - $withdrawn - $wallet->reserved_balance);
    }

    /**
     * Called by TaskService::approve() the moment an admin (or, later, an
     * AI verifier) approves a submission. This is the only way money ever
     * enters a participant's wallet.
     */
    public function creditReward(User $participant, float $amount, $submission): WalletTransaction
    {
        return DB::transaction(function () use ($participant, $amount, $submission) {
            $this->walletFor($participant);

            return WalletTransaction::create([
                'user_id' => $participant->id,
                'wallet_type' => 'participant',
                'type' => 'participant_reward_earned',
                'amount' => $amount,
                'reference_type' => get_class($submission),
                'reference_id' => $submission->id,
            ]);
        });
    }

    /**
     * Called by TaskService::revokeReward() to claw back an already-paid
     * reward. Records a negative manual_admin_adjustment rather than
     * deleting or reversing the original credit, so the full history (what
     * was earned, and separately what was taken back and why) stays intact
     * in the ledger.
     *
     * If the participant already withdrew this money, this can take their
     * availableBalance() negative - there's no real way to claw back funds
     * that already left the platform, so a negative balance is the honest
     * record of that: they now owe it back, to be recovered from future
     * earnings or otherwise, rather than the revocation silently failing or
     * being blocked.
     */
    public function revokeReward(User $participant, float $amount, $submission, string $reason, ?User $admin = null): WalletTransaction
    {
        return DB::transaction(function () use ($participant, $amount, $submission, $reason, $admin) {
            $this->walletFor($participant);

            return WalletTransaction::create([
                'user_id' => $participant->id,
                'wallet_type' => 'participant',
                'type' => 'manual_admin_adjustment',
                'amount' => -abs($amount),
                'reference_type' => get_class($submission),
                'reference_id' => $submission->id,
                'note' => $reason,
                'created_by' => $admin?->id,
            ]);
        });
    }

    /**
     * Called by ReferralService the moment a participant they referred
     * completes their one-time activation payment. Credited exactly like a
     * task reward - same wallet, same withdrawal flow - just tagged with a
     * different transaction type so it can be broken out on the Earnings
     * page and reported on separately.
     */
    public function creditReferralReward(User $referrer, float $amount, User $referredParticipant): WalletTransaction
    {
        return DB::transaction(function () use ($referrer, $amount, $referredParticipant) {
            $this->walletFor($referrer);

            return WalletTransaction::create([
                'user_id' => $referrer->id,
                'wallet_type' => 'participant',
                'type' => 'referral_reward_earned',
                'amount' => $amount,
                'reference_type' => get_class($referredParticipant),
                'reference_id' => $referredParticipant->id,
            ]);
        });
    }

    /**
     * Holds an amount out of the available balance the moment a withdrawal
     * is requested, so a participant can't request more than they actually
     * have while other requests are still pending.
     */
    public function reserveForWithdrawal(User $participant, float $amount, $withdrawalRequest): WalletTransaction
    {
        return DB::transaction(function () use ($participant, $amount, $withdrawalRequest) {
            $wallet = ParticipantWallet::where('user_id', $participant->id)->lockForUpdate()->first()
                ?? $this->walletFor($participant);

            $wallet->increment('reserved_balance', $amount);

            return WalletTransaction::create([
                'user_id' => $participant->id,
                'wallet_type' => 'participant',
                'type' => 'withdrawal_requested',
                'amount' => $amount,
                'reference_type' => get_class($withdrawalRequest),
                'reference_id' => $withdrawalRequest->id,
            ]);
        });
    }

    /**
     * Called when the scheduled payout job successfully transfers the
     * money: releases the hold and records the actual debit.
     */
    public function completeWithdrawal(User $participant, float $amount, $withdrawalRequest): WalletTransaction
    {
        return DB::transaction(function () use ($participant, $amount, $withdrawalRequest) {
            $wallet = ParticipantWallet::where('user_id', $participant->id)->lockForUpdate()->first();
            $wallet->decrement('reserved_balance', $amount);

            return WalletTransaction::create([
                'user_id' => $participant->id,
                'wallet_type' => 'participant',
                'type' => 'withdrawal_successful',
                'amount' => $amount,
                'reference_type' => get_class($withdrawalRequest),
                'reference_id' => $withdrawalRequest->id,
            ]);
        });
    }

    /**
     * Called when the transfer fails: releases the hold back to available
     * balance without recording a debit, since no money actually moved.
     */
    public function releaseFailedWithdrawal(User $participant, float $amount, $withdrawalRequest): WalletTransaction
    {
        return DB::transaction(function () use ($participant, $amount, $withdrawalRequest) {
            $wallet = ParticipantWallet::where('user_id', $participant->id)->lockForUpdate()->first();
            $wallet->decrement('reserved_balance', $amount);

            return WalletTransaction::create([
                'user_id' => $participant->id,
                'wallet_type' => 'participant',
                'type' => 'withdrawal_failed',
                'amount' => $amount,
                'reference_type' => get_class($withdrawalRequest),
                'reference_id' => $withdrawalRequest->id,
            ]);
        });
    }
}
