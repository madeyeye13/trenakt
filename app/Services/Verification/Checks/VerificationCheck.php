<?php

namespace App\Services\Verification\Checks;

use App\Models\CampaignSubmission;

/**
 * One deterministic, non-AI check run against a submission before it's ever
 * handed to the AI evaluator. Adding a new one is just a new class
 * registered in DeterministicVerifier::run() - nothing about a specific
 * campaign category is hardcoded here, since every check works off the
 * submission's actual requirement fields, not a category enum.
 */
interface VerificationCheck
{
    public function run(CampaignSubmission $submission): CheckResult;
}
