<?php

namespace App\Services\Verification;

use App\Jobs\VerifySubmissionWithAi;
use App\Models\CampaignSubmission;

/**
 * Doesn't call OpenAI itself - just hands the submission to a queued job so
 * the participant's Submit click returns immediately instead of waiting on
 * an external API. See VerifySubmissionWithAi for the actual deterministic
 * and AI evaluation, and the approve/reject/leave-for-review decision.
 */
class AiVerifier implements SubmissionVerifier
{
    public function verify(CampaignSubmission $submission): void
    {
        VerifySubmissionWithAi::dispatch($submission->id);
    }
}
