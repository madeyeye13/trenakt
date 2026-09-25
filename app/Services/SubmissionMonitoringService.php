<?php

namespace App\Services;

use App\Models\CampaignSubmission;
use App\Models\User;
use App\Notifications\RewardForfeited;
use App\Notifications\RewardReleased;
use App\Notifications\SubmissionRewardForfeitedAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Runs the "hold the reward, watch the link, release or forfeit" flow for
 * categories with requires_monitoring on (see the migration that adds it
 * to campaign_categories). Only ever touches a submission TaskService::
 * approve() already put into monitoring_status='holding' - nothing here
 * ever credits or debits a wallet for a submission that isn't in that
 * state, and a submission whose category doesn't require monitoring never
 * gets a monitoring_status at all, so its reward is paid exactly as it was
 * before this feature existed.
 */
class SubmissionMonitoringService
{
    public function __construct(
        protected ParticipantWalletService $wallet,
        protected PostAvailabilityChecker $checker,
    ) {
    }

    /**
     * How often an already-checked submission gets re-checked while still
     * inside its window. Short enough that even a category with a very
     * short monitoring window (admin can set minutes, not just hours) gets
     * more than one look, without hammering platforms for categories using
     * long windows. The scheduled command itself runs every five minutes -
     * see routes/console.php - this just skips a submission that was
     * checked recently until it's actually due again.
     */
    protected const RECHECK_INTERVAL_MINUTES = 15;

    /**
     * Called by the submissions:monitor-links scheduled command. Picks up
     * every submission still holding that's either never been checked, due
     * for its periodic recheck, or past its release time (the final,
     * decisive check).
     */
    public function checkDue(): void
    {
        $due = CampaignSubmission::query()
            ->where('monitoring_status', 'holding')
            ->where(function ($query) {
                $query->whereNull('last_monitor_checked_at')
                    ->orWhere('last_monitor_checked_at', '<=', now()->subMinutes(self::RECHECK_INTERVAL_MINUTES))
                    ->orWhere('monitoring_ends_at', '<=', now());
            })
            ->with(['participant', 'campaign.category.requirementFields'])
            ->get();

        foreach ($due as $submission) {
            $this->checkOne($submission);
        }
    }

    protected function checkOne(CampaignSubmission $submission): void
    {
        $removedDetail = null;

        foreach ($this->submittedUrls($submission) as $url) {
            $result = $this->checker->check($url);

            if ($result['status'] === 'removed') {
                $removedDetail = $result['detail'];
                break;
            }
        }

        $submission->forceFill([
            'last_monitor_checked_at' => now(),
            'monitor_check_attempts' => $submission->monitor_check_attempts + 1,
        ])->save();

        // A confirmed removal ends the hold immediately, even if the
        // window still has time left - no reason to make the participant
        // (or the platform) wait out the rest of it.
        if ($removedDetail) {
            $this->forfeit($submission, $removedDetail);
            return;
        }

        if (now()->greaterThanOrEqualTo($submission->monitoring_ends_at)) {
            $this->release($submission);
        }
    }

    /**
     * Every URL that needs watching for this submission: the category's
     * participant-facing 'url' requirement fields (e.g. a proof link a
     * non-post-mode category asks for), plus - for a post-mode campaign
     * (Reshare or Post-your-own-content) - the one proof link per platform
     * the participant submitted under answers.platform_links (see
     * Discover::submit()). A post-mode campaign only ever has the
     * platform_links entries, since Discover skips the category's generic
     * url field for those (it would just duplicate one of these). Any
     * single URL coming back 'removed' is enough to forfeit.
     */
    protected function submittedUrls(CampaignSubmission $submission): array
    {
        $urlFieldIds = $submission->campaign->category->requirementFields
            ->where('fills_for', 'participant')
            ->where('type', 'url')
            ->pluck('id');

        $genericUrls = $urlFieldIds
            ->map(fn ($id) => data_get($submission->answers, (string) $id))
            ->filter();

        $platformUrls = collect(data_get($submission->answers, 'platform_links', []))
            ->filter();

        return $genericUrls->merge($platformUrls)->values()->all();
    }

    public function release(CampaignSubmission $submission): void
    {
        DB::transaction(function () use ($submission) {
            $this->wallet->creditReward($submission->participant, (float) $submission->campaign->rate_per_participant, $submission);

            $submission->forceFill([
                'monitoring_status' => 'released',
                'reward_released_at' => now(),
            ])->save();
        });

        $submission->participant->notify(new RewardReleased($submission));
    }

    /**
     * No wallet action here - unlike TaskService::revokeReward() (an admin
     * clawing back a reward that was already paid), nothing was ever
     * credited for a submission still in 'holding', so there's nothing to
     * reverse. The submission's status stays 'approved', same reasoning as
     * revokeReward() - that's what keeps the participant from being able
     * to attempt this same campaign again (see TaskService::
     * participantIsEligible()).
     */
    public function forfeit(CampaignSubmission $submission, string $reason): void
    {
        $submission->forceFill([
            'monitoring_status' => 'forfeited',
            'monitor_forfeit_reason' => $reason,
        ])->save();

        $submission->participant->notify(new RewardForfeited($submission));

        // Same targeting as HumanReviewVerifier: whoever currently holds
        // the verify-submissions permission, not a hardcoded admin/super
        // admin pair.
        $reviewers = User::permission('verify-submissions')->get();

        if ($reviewers->isNotEmpty()) {
            Notification::send($reviewers, new SubmissionRewardForfeitedAlert($submission));
        }
    }
}
