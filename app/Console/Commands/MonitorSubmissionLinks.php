<?php

namespace App\Console\Commands;

use App\Services\SubmissionMonitoringService;
use Illuminate\Console\Command;

class MonitorSubmissionLinks extends Command
{
    protected $signature = 'submissions:monitor-links';

    protected $description = 'Checks post links for submissions held under category-level reward monitoring, and releases or forfeits the reward accordingly.';

    public function handle(SubmissionMonitoringService $monitoring): int
    {
        $monitoring->checkDue();

        $this->info('Monitoring check complete.');

        return self::SUCCESS;
    }
}
