<?php

namespace App\Services\Verification;

use App\Models\CampaignSubmission;

/**
 * A submission verifier decides what happens to a task submission right
 * after it's created. The only implementation today is HumanReviewVerifier,
 * which does nothing and leaves it for an admin to approve or reject from
 * the review queue.
 *
 * The point of this interface is that adding an AI-backed verifier later
 * (see AiVerifier, not yet built) is a matter of writing one new class and
 * flipping the `verification_mode` setting to 'ai' - nothing in TaskService,
 * the admin queue, notifications, or reward release needs to change. An AI
 * implementation should still fall back to leaving the submission untouched
 * (i.e. do nothing, same as HumanReviewVerifier) whenever it errors, times
 * out, or isn't confident enough to decide on its own.
 */
interface SubmissionVerifier
{
    /**
     * Called immediately after a submission is created. Implementations
     * that are confident enough may call TaskService::approve() or
     * TaskService::reject() directly; anything else should simply return
     * and leave the submission in 'submitted' status for a human to review.
     */
    public function verify(CampaignSubmission $submission): void;
}
