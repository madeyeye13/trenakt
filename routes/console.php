<?php

use Illuminate\Support\Facades\Schedule;

// Checked daily; the command itself checks the admin-configured payout day
// (or the "always open" setting) and no-ops on any other day.
Schedule::command('withdrawals:process')->dailyAt('02:00');

// Keeps the NGN-based conversion rates behind every participant-facing
// currency display (see CurrencyService) from going stale.
Schedule::command('exchange-rates:refresh')->dailyAt('01:00');

// Marks campaigns that have hit their target participant count as
// completed and deletes stored "post on your own page" media for them.
// Hourly is frequent enough that stored media doesn't linger long after a
// campaign fills, without hammering the database every few minutes.
Schedule::command('campaigns:complete-reached')->hourly();

// Checks post links for submissions held under a category's reward-
// monitoring window (see CampaignCategory.requires_monitoring) and
// releases or forfeits the reward. Every five minutes so even a category
// configured with a monitoring window of just a few minutes still gets
// checked promptly - SubmissionMonitoringService itself throttles how
// often any one submission is actually re-checked, so this doesn't mean
// every held submission is hit every five minutes.
Schedule::command('submissions:monitor-links')->everyFiveMinutes()->withoutOverlapping();
