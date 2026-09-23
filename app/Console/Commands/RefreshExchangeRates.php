<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class RefreshExchangeRates extends Command
{
    protected $signature = 'exchange-rates:refresh';

    protected $description = 'Pulls the latest NGN-based conversion rates so participant-facing currency displays stay current.';

    public function handle(ExchangeRateService $rates): int
    {
        $this->info('Refreshing exchange rates...');
        $rates->refresh();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
