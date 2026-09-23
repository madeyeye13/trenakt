<?php

use Illuminate\Support\Facades\Schedule;

// Checked daily; the command itself checks the admin-configured payout day
// (or the "always open" setting) and no-ops on any other day.
Schedule::command('withdrawals:process')->dailyAt('02:00');

// Keeps the NGN-based conversion rates behind every participant-facing
// currency display (see CurrencyService) from going stale.
Schedule::command('exchange-rates:refresh')->dailyAt('01:00');
