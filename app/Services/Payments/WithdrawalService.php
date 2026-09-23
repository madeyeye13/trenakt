<?php

namespace App\Services\Payments;

use App\Models\ParticipantBankAccount;
use App\Models\Setting;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Notifications\WithdrawalFailed;
use App\Notifications\WithdrawalProcessed;
use App\Services\ParticipantWalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WithdrawalService
{
    public function __construct(
        protected ParticipantWalletService $wallet,
        protected CountryPayoutResolver $payoutResolver,
    ) {
    }

    /**
     * Whether the withdraw button should even be clickable right now.
     * Profile + bank verification is always required; the day-of-week gate
     * is skippable by the admin via the withdrawal_always_open setting.
     */
    public function canRequestWithdrawal(User $participant): bool
    {
        return $participant->isEligibleForWithdrawal() && $this->isPayoutWindowOpen();
    }

    public function isPayoutWindowOpen(): bool
    {
        if (Setting::get('withdrawal_always_open', false)) {
            return true;
        }

        $payoutDay = Setting::get('payout_day', 'thursday');

        return strtolower(now()->format('l')) === strtolower((string) $payoutDay);
    }

    /**
     * Creates a withdrawal request and immediately reserves the amount out
     * of the participant's available balance, so they can't request the
     * same money twice while it's pending.
     */
    public function requestWithdrawal(User $participant, float $amount): WithdrawalRequest
    {
        if (! $participant->isEligibleForWithdrawal()) {
            throw ValidationException::withMessages([
                'withdrawal' => 'Please complete your profile and add a verified bank account before withdrawing.',
            ]);
        }

        if (! $this->isPayoutWindowOpen()) {
            throw ValidationException::withMessages([
                'withdrawal' => 'Withdrawals aren\'t open right now. Please try again on the next payout day.',
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Enter an amount greater than zero.',
            ]);
        }

        if ($amount > $this->wallet->availableBalance($participant)) {
            throw ValidationException::withMessages([
                'amount' => 'You cannot withdraw more than your available balance.',
            ]);
        }

        return DB::transaction(function () use ($participant, $amount) {
            $withdrawal = WithdrawalRequest::create([
                'user_id' => $participant->id,
                'amount' => $amount,
                'status' => 'pending',
                'requested_at' => now(),
            ]);

            $this->wallet->reserveForWithdrawal($participant, $amount, $withdrawal);

            return $withdrawal;
        });
    }

    /**
     * Runs on the scheduled payout day (see the trenakt:process-withdrawals
     * command): sends every pending request to Paystack and records the
     * outcome. One request's failure never stops the batch.
     */
    public function processPendingWithdrawals(): void
    {
        WithdrawalRequest::where('status', 'pending')
            ->with(['user', 'user.bankAccount'])
            ->chunkById(50, function ($withdrawals) {
                foreach ($withdrawals as $withdrawal) {
                    $this->processOne($withdrawal);
                }
            });
    }

    protected function processOne(WithdrawalRequest $withdrawal): void
    {
        $participant = $withdrawal->user;
        $bankAccount = $participant->bankAccount;

        $withdrawal->forceFill(['status' => 'processing'])->save();

        if (! $bankAccount || ! $bankAccount->isVerified()) {
            $this->fail($withdrawal, $participant, 'No verified bank account on file.');

            return;
        }

        $gateway = $this->payoutResolver->gatewayFor($participant);

        if (! $gateway) {
            $this->fail($withdrawal, $participant, 'Withdrawals for this participant\'s country are not set up yet.');

            return;
        }

        try {
            $recipientCode = $bankAccount->recipient_code
                ?? $this->registerRecipient($bankAccount, $gateway);

            $reference = 'wd_' . $withdrawal->id . '_' . Str::random(8);

            $result = $gateway->initiateTransfer(
                $recipientCode,
                (float) $withdrawal->amount,
                $reference,
                'Trenakt earnings withdrawal'
            );

            if (in_array($result['status'], ['success', 'pending'], true)) {
                $this->succeed($withdrawal, $participant, $result['transfer_code'] ?? $reference);
            } else {
                $this->fail($withdrawal, $participant, 'Transfer was not accepted by the payment gateway.');
            }
        } catch (\Throwable $e) {
            Log::error('Withdrawal transfer failed', [
                'withdrawal_id' => $withdrawal->id,
                'user_id' => $participant->id,
                'message' => $e->getMessage(),
            ]);

            $this->fail($withdrawal, $participant, 'The payment gateway could not be reached.');
        }
    }

    protected function registerRecipient(ParticipantBankAccount $bankAccount, PaystackService $gateway): string
    {
        $recipientCode = $gateway->createTransferRecipient(
            $bankAccount->account_number,
            $bankAccount->bank_code,
            $bankAccount->account_name,
            $bankAccount->isMobileMoney() ? 'mobile_money' : 'nuban',
        );

        $bankAccount->forceFill(['recipient_code' => $recipientCode])->save();

        return $recipientCode;
    }

    protected function succeed(WithdrawalRequest $withdrawal, User $participant, string $transferId): void
    {
        DB::transaction(function () use ($withdrawal, $participant, $transferId) {
            $this->wallet->completeWithdrawal($participant, (float) $withdrawal->amount, $withdrawal);

            $withdrawal->forceFill([
                'status' => 'paid',
                'gateway_transfer_id' => $transferId,
                'processed_at' => now(),
            ])->save();
        });

        $participant->notify(new WithdrawalProcessed($withdrawal));
    }

    protected function fail(WithdrawalRequest $withdrawal, User $participant, string $reason): void
    {
        DB::transaction(function () use ($withdrawal, $participant, $reason) {
            $this->wallet->releaseFailedWithdrawal($participant, (float) $withdrawal->amount, $withdrawal);

            $withdrawal->forceFill([
                'status' => 'failed',
                'failure_reason' => $reason,
                'processed_at' => now(),
            ])->save();
        });

        $participant->notify(new WithdrawalFailed($withdrawal));
    }
}
