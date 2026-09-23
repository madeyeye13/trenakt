<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\Verification\HumanReviewVerifier;
use App\Services\Verification\SubmissionVerifier;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Whichever verifier TaskService::submit() is handed is decided
        // here, from the admin-configurable `verification_mode` setting.
        // Today only 'human' exists; adding an AI-backed verifier later
        // means writing that class and adding one more match arm - nothing
        // else in the app needs to change. Any unrecognised or future value
        // falls back to human review.
        $this->app->bind(SubmissionVerifier::class, function () {
            return match (Setting::get('verification_mode', 'human')) {
                default => new HumanReviewVerifier(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
