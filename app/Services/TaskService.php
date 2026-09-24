<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignSubmission;
use App\Models\RejectionReason;
use App\Models\User;
use App\Notifications\RewardRevoked;
use App\Notifications\TaskApproved;
use App\Notifications\TaskRejected;
use App\Services\Payments\ActivationFeeService;
use App\Services\Verification\SubmissionVerifier;
use Illuminate\Support\Facades\DB;
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

        // Notifying reviewers is the verifier's job now, not this method's -
        // see HumanReviewVerifier (notifies immediately, every time) and
        // VerifySubmissionWithAi (only notifies when AI couldn't resolve it
        // on its own). That split is what lets AI mode avoid pinging admins
        // about submissions it ends up handling by itself.
        $verifier->verify($submission);

        return $submission;
    }

    /**
     * $admin is accepted for context/future auditing but never used in the
     * body below - nullable so the AI verifier's queued job can call this
     * the same way an admin's own click does, without a real acting user.
     */
    public function approve(CampaignSubmission $submission, ?User $admin = null): void
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

    /**
     * $admin is accepted for context/future auditing but never used in the
     * body below - nullable so the AI verifier's queued job can call this
     * the same way an admin's own click does, without a real acting user.
     */
    public function reject(CampaignSubmission $submission, ?User $admin, RejectionReason $reason, ?string $note = null): void
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

    /**
     * Claws back a reward on a submission that was already approved and
     * paid. The submission's status stays 'approved' - see the migration
     * that adds reward_revoked_at - so this does not free up the campaign's
     * capacity or let the participant resubmit; only a genuine rejection at
     * review time does that (see participantIsEligible()).
     *
     * $admin is nullable for the same reason approve()/reject() accept a
     * nullable one, even though only an admin's own click calls this today.
     */
    public function revokeReward(CampaignSubmission $submission, ?User $admin, string $reason): void
    {
        if ($submission->status !== 'approved') {
            throw ValidationException::withMessages([
                'submission' => 'Only an approved submission with a paid reward can have it revoked.',
            ]);
        }

        if ($submission->reward_revoked_at) {
            throw ValidationException::withMessages([
                'submission' => 'This reward has already been revoked.',
            ]);
        }

        DB::transaction(function () use ($submission, $admin, $reason) {
            $this->wallet->revokeReward($submission->participant, (float) $submission->campaign->rate_per_participant, $submission, $reason, $admin);

            $submission->forceFill([
                'reward_revoked_at' => now(),
                'reward_revocation_reason' => $reason,
                'revoked_by' => $admin?->id,
            ])->save();
        });

        $submission->participant->notify(new RewardRevoked($submission));
    }
}
