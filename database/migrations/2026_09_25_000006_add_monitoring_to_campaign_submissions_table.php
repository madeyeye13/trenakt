<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The per-submission side of category-level reward monitoring (see the
 * migration adding requires_monitoring/monitoring_minutes to
 * campaign_categories). These columns are only ever set when that
 * category flag was on at the moment TaskService::approve() ran - for
 * every other submission monitoring_status stays null forever, and reward
 * payout behaves exactly as it did before this feature existed.
 *
 * This is deliberately a separate set of columns from
 * reward_revoked_at/reward_revocation_reason/revoked_by (added earlier for
 * an admin manually clawing back an already-paid reward): a submission
 * that gets forfeited by monitoring never had its reward credited in the
 * first place, so there's no wallet clawback, no negative-balance risk,
 * and no "already withdrawn" concern - see
 * SubmissionMonitoringService::forfeit().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_submissions', function (Blueprint $table) {
            // null = not applicable (monitoring wasn't required at approval time).
            // 'holding' = approved, reward not yet credited, still being watched.
            // 'released' = window passed clean, reward credited.
            // 'forfeited' = a submitted link was found removed/private; reward never credited.
            $table->string('monitoring_status')->nullable()->after('revoked_by');
            $table->timestamp('monitoring_started_at')->nullable()->after('monitoring_status');
            $table->timestamp('monitoring_ends_at')->nullable()->after('monitoring_started_at');
            $table->timestamp('last_monitor_checked_at')->nullable()->after('monitoring_ends_at');
            $table->unsignedInteger('monitor_check_attempts')->default(0)->after('last_monitor_checked_at');
            $table->timestamp('reward_released_at')->nullable()->after('monitor_check_attempts');
            $table->text('monitor_forfeit_reason')->nullable()->after('reward_released_at');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'monitoring_status',
                'monitoring_started_at',
                'monitoring_ends_at',
                'last_monitor_checked_at',
                'monitor_check_attempts',
                'reward_released_at',
                'monitor_forfeit_reason',
            ]);
        });
    }
};
