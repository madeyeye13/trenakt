<?php

namespace App\Services;

use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\BusinessWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function walletFor(User $user): BusinessWallet
    {
        return BusinessWallet::firstOrCreate(['user_id' => $user->id]);
    }

    public function availableBalance(User $user): float
    {
        $wallet = $this->walletFor($user);

        $credits = WalletTransaction::where('user_id', $user->id)
            ->where('wallet_type', 'business')
            ->whereIn('type', ['wallet_funded'])
            ->sum('amount');

        $adjustments = WalletTransaction::where('user_id', $user->id)
            ->where('wallet_type', 'business')
            ->where('type', 'manual_admin_adjustment')
            ->sum('amount');

        $spent = WalletTransaction::where('user_id', $user->id)
            ->where('wallet_type', 'business')
            ->where('type', 'campaign_spend')
            ->sum('amount');

        return (float) ($credits + $adjustments - $spent - $wallet->reserved_balance);
    }

    public function fund(User $user, float $amount, string $currency, $reference = null): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $currency, $reference) {
            $this->walletFor($user);

            return WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_type' => 'business',
                'type' => 'wallet_funded',
                'amount' => $amount,
                'currency' => $currency,
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);
        });
    }

    public function reserve(User $user, float $amount, $campaign): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $campaign) {
            $wallet = BusinessWallet::where('user_id', $user->id)->lockForUpdate()->first()
                ?? $this->walletFor($user);

            if ($this->availableBalance($user) < $amount) {
                throw new InsufficientWalletBalanceException(
                    'Your wallet balance is not enough to cover this campaign budget. Please fund your wallet first.'
                );
            }

            $wallet->increment('reserved_balance', $amount);

            return WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_type' => 'business',
                'type' => 'campaign_reservation',
                'amount' => $amount,
                'reference_type' => get_class($campaign),
                'reference_id' => $campaign->id,
            ]);
        });
    }

    public function release(User $user, float $amount, $campaign): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $campaign) {
            $wallet = BusinessWallet::where('user_id', $user->id)->lockForUpdate()->first();
            $wallet->decrement('reserved_balance', $amount);

            return WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_type' => 'business',
                'type' => 'campaign_reservation_released',
                'amount' => $amount,
                'reference_type' => get_class($campaign),
                'reference_id' => $campaign->id,
            ]);
        });
    }

    public function spend(User $user, float $amount, float $feeAmount, $campaign): void
    {
        DB::transaction(function () use ($user, $amount, $feeAmount, $campaign) {
            $wallet = BusinessWallet::where('user_id', $user->id)->lockForUpdate()->first();
            $wallet->decrement('reserved_balance', $amount);

            WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_type' => 'business',
                'type' => 'campaign_spend',
                'amount' => $amount,
                'reference_type' => get_class($campaign),
                'reference_id' => $campaign->id,
            ]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_type' => 'business',
                'type' => 'trenakt_fee',
                'amount' => $feeAmount,
                'reference_type' => get_class($campaign),
                'reference_id' => $campaign->id,
                'note' => 'Platform fee included in campaign spend.',
            ]);
        });
    }

    public function adjust(User $user, float $amount, string $note, User $admin): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $note, $admin) {
            $this->walletFor($user);

            return WalletTransaction::create([
                'user_id' => $user->id,
                'wallet_type' => 'business',
                'type' => 'manual_admin_adjustment',
                'amount' => $amount,
                'note' => $note,
                'created_by' => $admin->id,
            ]);
        });
    }
}