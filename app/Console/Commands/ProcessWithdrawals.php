<?php

namespace App\Console\Commands;

use App\Services\Payments\WithdrawalService;
use Illuminate\Console\Command;

class ProcessWithdrawals extends Command
{
    protected $signature = 'withdrawals:process {--force : Run even if today is not the configured payout day}';

    protected $description = 'Sends every pending withdrawal request to the payment gateway, on the admin-configured payout day.';

    public function handle(WithdrawalService $withdrawals): int
    {
        if (! $this->option('force') && ! $withdrawals->isPayoutWindowOpen()) {
            $this->info('Today is not the configured payout day. Nothing to do.');

            return self::SUCCESS;
        }

        $this->info('Processing pending withdrawals...');
        $withdrawals->processPendingWithdrawals();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
