<?php

namespace App\Services\Payments;

use App\Models\ActivationPayment;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\AccountActivated;
use App\Services\ReferralService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Governs the one-time participant "activation fee": whether it's
 * currently required for a given participant, and the Paystack checkout
 * that collects it. Turning this on for the first time is also what
 * reveals the referral system - see ReferralService - since there's
 * nothing to reward a referral with until real money is changing hands.
 */
class ActivationFeeService
{
    public function __construct(protected PaystackService $paystack, protected ReferralService $referrals)
    {
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get('activation_fee_enabled', false);
    }

    public function amount(): float
    {
        return (float) Setting::get('activation_fee_amount', 0);
    }

    /**
     * Whether $participant currently has to pay before they can accept or
     * submit tasks. Always false once they've paid or while the fee is
     * off. If admin chose to grandfather existing participants in, it's
     * also false for anyone who registered before the fee was first ever
     * turned on (Admin\Settings\Index::save() stamps that cutoff).
     */
    public function isRequiredFor(User $participant): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        if ($participant->hasPaidActivationFee()) {
            return false;
        }

        if (! (bool) Setting::get('activation_fee_grandfather_existing', false)) {
            return true;
        }

        $requiredFrom = Setting::get('activation_fee_required_from');

        return $requiredFrom && $participant->created_at?->gte(Carbon::parse($requiredFrom));
    }

    /**
     * Starts a Paystack checkout for the activation fee and records a
     * pending ActivationPayment to reconcile against once the callback (or
     * webhook) verifies it.
     */
    public function initialize(User $participant, string $callbackUrl): array
    {
        $reference = 'act_' . Str::uuid();
        $amount = $this->amount();

        ActivationPayment::create([
            'user_id' => $participant->id,
            'reference' => $reference,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        return $this->paystack->initialize($participant->email, $amount, $callbackUrl, $reference);
    }

    /**
     * Verifies a Paystack reference against our pending ActivationPayment
     * and, on success, marks it paid and rewards the referrer (if any).
     * Locked and idempotent, mirroring WalletFundingService::complete(), so
     * a callback and a webhook racing each other only ever process the
     * payment once.
     */
    public function complete(string $reference, bool $successful, float $verifiedAmount, ?string $gatewayTransactionId = null): ?ActivationPayment
    {
        return DB::transaction(function () use ($reference, $successful, $verifiedAmount, $gatewayTransactionId) {
            $payment = ActivationPayment::where('reference', $reference)->lockForUpdate()->first();

            if (! $payment || $payment->status !== 'pending') {
                return $payment;
            }

            $amountMatches = round($verifiedAmount, 2) === round((float) $payment->amount, 2);

            if (! $successful || ! $amountMatches) {
                $payment->update([
                    'status' => 'failed',
                    'gateway_transaction_id' => $gatewayTransactionId,
                    'verified_at' => now(),
                ]);

                return $payment;
            }

            $payment->update([
                'status' => 'successful',
                'gateway_transaction_id' => $gatewayTransactionId,
                'verified_at' => now(),
            ]);

            $payment->user->notify(new AccountActivated($payment));
            $this->referrals->rewardReferrerFor($payment->user);

            return $payment;
        });
    }
}
