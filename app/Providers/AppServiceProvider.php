<?php

namespace App\Providers;

use App\Models\Setting;
use App\Services\Verification\AiEvaluator;
use App\Services\Verification\AiVerifier;
use App\Services\Verification\HumanReviewVerifier;
use App\Services\Verification\OpenAiEvaluator;
use App\Services\Verification\SubmissionVerifier;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // The AI evaluator is bound behind its own interface, separately
        // from SubmissionVerifier, so swapping providers later (a different
        // model, a non-OpenAI vendor) is a one-line change here - nothing in
        // AiVerifier or the verification job needs to know which vendor
        // answered.
        $this->app->bind(AiEvaluator::class, OpenAiEvaluator::class);

        // Whichever verifier TaskService::submit() is handed is decided
        // here, from the admin-configurable `verification_mode` setting.
        // Adding a verifier later means writing that class and adding one
        // more match arm - nothing else in the app needs to change. Any
        // unrecognised or future value falls back to human review.
        $this->app->bind(SubmissionVerifier::class, function () {
            return match (Setting::get('verification_mode', 'human')) {
                'ai' => new AiVerifier(),
                default => new HumanReviewVerifier(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // super_admin always has every permission, including ones added to
        // AdminPermissions after this role was created - it never needs its
        // permission list kept in sync by hand. Every other role is checked
        // normally against whatever permissions a super admin gave it.
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}
