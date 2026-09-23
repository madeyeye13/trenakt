<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignSubmission;
use App\Models\RejectionReason;
use App\Models\User;
use App\Notifications\TaskApproved;
use App\Notifications\TaskRejected;
use App\Notifications\TaskSubmissionReceived;
use App\Services\Payments\ActivationFeeService;
use App\Services\Verification\SubmissionVerifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function __construct(protected ParticipantWalletService $wallet, protected ActivationFeeService $activationFee)
    {
    }

    /**
     * Whether $participant can currently see/accept $campaign as a task:
     * it's live, matches their targeting, still has room, and they don't
     * already have an active (non-rejected) submission on it. A prior
     * rejected submission does NOT block eligibility, so retrying is always
     * possible.
     */
    public function participantIsEligible(Campaign $campaign, User $participant): bool
    {
        if ($campaign->status !== 'approved') {
            return false;
        }

        if (! $this->matchesTargeting($campaign->targeting, $participant)) {
            return false;
        }

        if (! $this->hasCapacity($campaign)) {
            return false;
        }

        $hasActiveSubmission = CampaignSubmission::where('campaign_id', $campaign->id)
            ->where('participant_id', $participant->id)
            ->where('status', '!=', 'rejected')
            ->exists();

        return ! $hasActiveSubmission;
    }

    public function hasCapacity(Campaign $campaign): bool
    {
        $activeCount = CampaignSubmission::where('campaign_id', $campaign->id)
            ->where('status', '!=', 'rejected')
            ->count();

        return $activeCount < $campaign->target_participants;
    }

    /**
     * The SQL-expressible half of participantIsEligible(): live campaigns
     * this participant hasn't already got an active (non-rejected)
     * submission on, and that still have room. Used by the Discover page so
     * it isn't running a query per campaign card. Age/country targeting
     * still needs to be applied on the result in PHP (see
     * participantIsEligible()) since it isn't worth a correlated subquery
     * for what's normally a small candidate set.
     */
    public function eligibleCampaignsQuery(User $participant): \Illuminate\Database\Eloquent\Builder
    {
        $activeSubmissionCampaignIds = CampaignSubmission::where('participant_id', $participant->id)
            ->where('status', '!=', 'rejected')
            ->pluck('campaign_id');

        return Campaign::query()
            ->where('status', 'approved')
            ->whereNotIn('id', $activeSubmissionCampaignIds)
            ->whereRaw(
                '(select count(*) from campaign_submissions cs where cs.campaign_id = campaigns.id and cs.status != ?) < campaigns.target_participants',
                ['rejected']
            );
    }

    /**
     * Whether $targeting excludes $participant, given the demographic data
     * we actually collect. We don't collect gender on the participant
     * profile, so gender targeting is never enforced here - a campaign
     * targeted at a gender is currently shown to everyone.
     */
    public function matchesTargeting(?\App\Models\CampaignTargeting $targeting, User $participant): bool
    {
        if (! $targeting) {
            return true;
        }

        $age = $participant->age();

        if ($targeting->min_age && (! $age || $age < $targeting->min_age)) {
            return false;
        }

        if ($targeting->max_age && (! $age || $age > $targeting->max_age)) {
            return false;
        }

        if (! empty($targeting->country_ids) && ! in_array($participant->country_id, $targeting->country_ids, true)) {
            return false;
        }

        return true;
    }

    /**
     * The participant's most recent rejected submission for a campaign, if
     * any - used by Discover to show why a retry-able campaign was rejected
     * last time.
     */
    public function latestRejection(Campaign $campaign, User $participant): ?CampaignSubmission
    {
        return CampaignSubmission::where('campaign_id', $campaign->id)
            ->where('participant_id', $participant->id)
            ->where('status', 'rejected')
            ->latest('reviewed_at')
            ->first();
    }

    /**
     * Records a participant's submission for a task and hands it to the
     * configured verifier (human review by default). Throws a validation
     * exception rather than silently failing if they're no longer eligible
     * (e.g. the campaign filled up, or they already have an active
     * submission) between viewing the task and submitting it.
     */
    public function submit(Campaign $campaign, User $participant, array $answers, SubmissionVerifier $verifier): CampaignSubmission
    {
        // Defense in depth: Discover already routes an unpaid, activation-
        // required participant to the activation checkout instead of the
        // submission form, but this is the one path every submission has
        // to go through, so it's re-checked here too.
        if ($this->activationFee->isRequiredFor($participant)) {
            throw ValidationException::withMessages([
                'campaign' => 'Activate your account before submitting tasks.',
            ]);
        }

        if (! $this->participantIsEligible($campaign, $participant)) {
            throw ValidationException::withMessages([
                'campaign' => 'This task is no longer available to you.',
            ]);
        }

        $submission = DB::transaction(function () use ($campaign, $participant, $answers) {
            return CampaignSubmission::create([
                'campaign_id' => $campaign->id,
                'participant_id' => $participant->id,
                'status' => 'submitted',
                'answers' => $answers,
                'submitted_at' => now(),
            ]);
        });

        $reviewers = User::role(['admin', 'super_admin'])->get();

        if ($reviewers->isNotEmpty()) {
            Notification::send($reviewers, new TaskSubmissionReceived($submission));
        }

        $verifier->verify($submission);

        return $submission;
    }

    public function approve(CampaignSubmission $submission, User $admin): void
    {
        if ($submission->status !== 'submitted') {
            throw ValidationException::withMessages([
                'submission' => 'This submission is no longer pending review.',
            ]);
        }

        DB::transaction(function () use ($submission, $admin) {
            $this->wallet->creditReward($submission->participant, (float) $submission->campaign->rate_per_participant, $submission);

            $submission->forceFill([
                'status' => 'approved',
                'reviewed_at' => now(),
            ])->save();
        });

        $submission->participant->notify(new TaskApproved($submission));
    }

    public function reject(CampaignSubmission $submission, User $admin, RejectionReason $reason, ?string $note = null): void
    {
        if ($submission->status !== 'submitted') {
            throw ValidationException::withMessages([
                'submission' => 'This submission is no longer pending review.',
            ]);
        }

        $finalNote = $note ?: $reason->label;

        DB::transaction(function () use ($submission, $reason, $finalNote) {
            $submission->forceFill([
                'status' => 'rejected',
                'reviewed_at' => now(),
                'rejection_reason_id' => $reason->id,
                'rejection_reason' => $finalNote,
            ])->save();
        });

        // No wallet action here on purpose: nothing was ever credited for a
        // submitted-but-not-yet-approved task, so there's nothing to
        // reverse. The participant is simply free to attempt this campaign
        // again (see participantIsEligible()).
        $submission->participant->notify(new TaskRejected($submission));
    }
}
