<?php

namespace App\Services\Verification;

use App\Models\CampaignSubmission;
use App\Models\User;
use App\Notifications\TaskSubmissionReceived;
use Illuminate\Support\Facades\Notification;

/**
 * The default verifier: makes no decision at all, it just makes sure a
 * human finds out right away. Whoever holds the verify-submissions
 * permission gets notified immediately - not a hardcoded 'admin'/
 * 'super_admin' pair, so a role like "Task Verifier" a super admin creates
 * on the Roles page starts receiving these the moment it's assigned, no
 * code change needed.
 *
 * This notification used to fire unconditionally from TaskService::submit()
 * regardless of mode. It's been moved here so that in AI mode, reviewers
 * are only pinged for the submissions AI genuinely couldn't resolve on its
 * own (see VerifySubmissionWithAi and SubmissionNeedsHumanReview) instead of
 * getting a notification for every single submission whether it needed them
 * or not.
 */
class HumanReviewVerifier implements SubmissionVerifier
{
    public function verify(CampaignSubmission $submission): void
    {
        $reviewers = User::permission('verify-submissions')->get();

        if ($reviewers->isNotEmpty()) {
            Notification::send($reviewers, new TaskSubmissionReceived($submission));
        }
    }
}
