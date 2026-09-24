<?php

namespace App\Services\Payments;


use App\Models\User;
use App\Models\WalletFundingRequest;
use App\Models\WalletTransaction;
use App\Notifications\BusinessWalletFunded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class WalletFundingService
{
    public function __construct(protected \App\Services\WalletService $wallet)
    {
    }

    public function initiate(int $userId, string $gateway, float $amount): WalletFundingRequest
    {
        return WalletFundingRequest::create([
            'user_id' => $userId,
            'gateway' => $gateway,
            'reference' => 'trk_' . Str::uuid(),
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    public function complete(WalletFundingRequest $fundingRequest, bool $successful, float $verifiedAmount, ?string $gatewayTransactionId = null): bool
    {
        return DB::transaction(function () use ($fundingRequest, $successful, $verifiedAmount, $gatewayTransactionId) {
            $locked = WalletFundingRequest::where('id', $fundingRequest->id)->lockForUpdate()->first();

            // Already processed by an earlier callback or webhook call, do nothing.
            if (! $locked || $locked->status !== 'pending') {
                return false;
            }

            $amountMatches = round($verifiedAmount, 2) === round((float) $locked->amount, 2);

            if (! $successful || ! $amountMatches) {
                $locked->update([
                    'status' => 'failed',
                    'gateway_transaction_id' => $gatewayTransactionId,
                    'verified_at' => now(),
                ]);

                WalletTransaction::create([
                    'user_id' => $locked->user_id,
                    'wallet_type' => 'business',
                    'type' => 'wallet_funding_failed',
                    'amount' => $locked->amount,
                    'currency' => 'NGN',
                    'reference_type' => WalletFundingRequest::class,
                    'reference_id' => $locked->id,
                    'note' => 'Payment via ' . $locked->gateway . ' could not be verified.',
                ]);

                $locked->user->notify(new \App\Notifications\WalletFundingFailed($locked));

                return false;
            }

            $locked->update([
                'status' => 'successful',
                'gateway_transaction_id' => $gatewayTransactionId,
                'verified_at' => now(),
            ]);

            $this->wallet->fund($locked->user, $locked->amount, 'NGN', $locked);

            $locked->user->notify(new \App\Notifications\WalletFunded($locked));

            $admins = User::permission('manage-wallet-fundings')->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new BusinessWalletFunded($locked));
            }

            return true;
        });
    }
}
