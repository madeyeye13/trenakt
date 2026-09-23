<?php

namespace App\Services\Verification;

use App\Models\CampaignSubmission;

/**
 * The default verifier: makes no decision at all. The submission simply
 * stays in 'submitted' status and shows up in the admin review queue,
 * exactly as it does today.
 */
class HumanReviewVerifier implements SubmissionVerifier
{
    public function verify(CampaignSubmission $submission): void
    {
        // Intentionally a no-op. Human review happens through the admin
        // submissions queue, not here.
    }
}
