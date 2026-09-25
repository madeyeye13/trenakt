<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an admin turn on "reward monitoring" per category: when on, a
 * submission's reward isn't credited the moment it's approved (see
 * TaskService::approve()) - it's held until monitoring_minutes has passed
 * with the participant's posted link(s) still confirmed live (see
 * SubmissionMonitoringService). monitoring_minutes is deliberately a plain
 * integer rather than a fixed "hours" column, since it needs to hold either
 * unit (an admin might set 30 minutes or 2 days) - the admin UI converts
 * whatever they enter into minutes before saving.
 *
 * Only meaningful alongside supports_post_modes (there's no link to watch
 * otherwise), but stored independently so nothing here assumes that.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_categories', function (Blueprint $table) {
            $table->boolean('requires_monitoring')->default(false)->after('platform_bonus_amount');
            $table->unsignedInteger('monitoring_minutes')->nullable()->after('requires_monitoring');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_categories', function (Blueprint $table) {
            $table->dropColumn(['requires_monitoring', 'monitoring_minutes']);
        });
    }
};
