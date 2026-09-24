<?php

namespace App\Jobs;

use App\Models\CampaignSubmission;
use App\Models\RejectionReason;
use App\Models\Setting;
use App\Models\SubmissionVerification;
use App\Models\User;
use App\Notifications\SubmissionNeedsHumanReview;
use App\Services\TaskService;
use App\Services\Verification\AiEvaluationResult;
use App\Services\Verification\AiEvaluator;
use App\Services\Verification\Checks\CheckResult;
use App\Services\Verification\DeterministicVerifier;
use App\Services\Verification\SubmissionEvaluationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * The actual work behind AI-assisted verification, run off the queue so a
 * participant's Submit click never waits on OpenAI. Order of operations,
 * deliberately in this sequence:
 *
 *   1. Deterministic checks (required evidence present, URLs well-formed,
 *      files real). A blocking failure here ends the attempt immediately -
 *      the AI is never even asked, and the submission is left at
 *      'submitted' for a human. Deterministic requirements always take
 *      precedence over whatever confidence the AI would have reported.
 *   2. Only submissions that pass step 1 go to the AI. Its raw opinion is
 *      never trusted directly - it only becomes an actual decision once
 *      it clears an admin-configured confidence threshold.
 *   3. Anything uncertain - low confidence, an AI error, timeout, missing
 *      or invalid key, or a malformed response - leaves the submission at
 *      'submitted', exactly where HumanReviewVerifier would have left it.
 *
 * Every attempt (success or failure) writes a SubmissionVerification audit
 * row, so there's always a record of how a decision was reached.
 */
class VerifySubmissionWithAi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * A genuine failure should fall back to human review immediately, not
     * retry and delay that fallback further - see the catch block below,
     * which already treats every failure as "leave it for a human".
     */
    public int $tries = 1;

    public function __construct(public int $submissionId)
    {
    }

    public function handle(DeterministicVerifier $deterministic, AiEvaluator $ai, TaskService $tasks): void
    {
        $submission = CampaignSubmission::with([
            'campaign.category.requirementFields',
            'campaign.customFields',
            'campaign.requirementAnswers.field',
        ])->find($this->submissionId);

        // Might already have been handled by a human in the time it took
        // this job to run, or the record could be gone entirely - either
        // way there's nothing left for AI verification to do.
        if (! $submission || $submission->status !== 'submitted') {
            return;
        }

        $deterministicResults = $deterministic->run($submission);

        if ($deterministic->hasBlockingFailure($deterministicResults)) {
            $this->recordAudit($submission, [
                'ai_verdict' => null,
                'confidence' => null,
                'verdict' => 'needs_review',
                'deterministic_checks' => $this->serializeChecks($deterministicResults),
                'ai_checks' => null,
                'reasons' => ['A deterministic check failed - see deterministic_checks for detail. Never auto-approved or auto-rejected on a deterministic failure alone.'],
                'evidence_considered' => null,
                'raw_response' => null,
                'error' => null,
            ]);

            $this->notifyReviewers($submission, 'An automatic check flagged a problem before AI even looked at it (e.g. missing required evidence, or a broken link) - see the AI check panel on this submission for detail.');

            return;
        }

        $request = $this->buildEvaluationRequest($submission, $deterministic->advisoryNotes($deterministicResults));

        try {
            $result = $ai->evaluate($request);
        } catch (\Throwable $e) {
            Log::warning('AI verification failed, leaving submission for human review', [
                'submission_id' => $submission->id,
                'error' => $e->getMessage(),
            ]);

            $this->recordAudit($submission, [
                'ai_verdict' => null,
                'confidence' => null,
                'verdict' => 'needs_review',
                'deterministic_checks' => $this->serializeChecks($deterministicResults),
                'ai_checks' => null,
                'reasons' => null,
                'evidence_considered' => null,
                'raw_response' => null,
                'error' => $e->getMessage(),
            ]);

            $this->notifyReviewers($submission, 'AI verification couldn\'t complete (a connection, key, or configuration issue), so no automatic decision could be made.');

            return;
        }

        $finalVerdict = $this->applyThresholds($result);

        $this->recordAudit($submission, [
            'ai_verdict' => $result->verdict,
            'confidence' => $result->confidence,
            'verdict' => $finalVerdict,
            'deterministic_checks' => $this->serializeChecks($deterministicResults),
            'ai_checks' => $result->checks,
            'reasons' => $result->reasons,
            'evidence_considered' => $result->evidenceConsidered,
            'raw_response' => $result->raw,
            'error' => null,
        ]);

        if ($finalVerdict === 'approved') {
            $tasks->approve($submission);

            return;
        }

        if ($finalVerdict === 'rejected') {
            $reason = $this->rejectionReason();

            // Auto-reject is opt-in - a Super Admin has to explicitly pick a
            // default reason in Settings before AI is allowed to reject
            // anything on its own. With none configured, fall through and
            // leave the submission at 'submitted' even though the AI was
            // confident it should be rejected - and let a reviewer know why
            // it's sitting there instead of silently stalling.
            if ($reason) {
                $tasks->reject($submission, null, $reason, $result->reasons !== [] ? implode(' ', $result->reasons) : null);

                return;
            }

            $this->notifyReviewers($submission, 'AI was confident this should be rejected, but no default rejection reason is configured in Settings, so it\'s been left for you to decide.');

            return;
        }

        // needs_review: AI genuinely wasn't confident enough either way.
        $this->notifyReviewers(
            $submission,
            $result->reasons !== [] ? implode(' ', $result->reasons) : 'AI reviewed this but wasn\'t confident enough to decide on its own.',
        );
    }

    /**
     * The one place AI mode ever pings a human: whenever this job is about
     * to leave a submission at 'submitted' instead of resolving it. Never
     * called for a submission AI successfully auto-approved or auto-
     * rejected, so reviewers only hear about the ones that actually need
     * them - see SubmissionNeedsHumanReview.
     */
    protected function notifyReviewers(CampaignSubmission $submission, string $reason): void
    {
        $reviewers = User::permission('verify-submissions')->get();

        if ($reviewers->isNotEmpty()) {
            Notification::send($reviewers, new SubmissionNeedsHumanReview($submission, $reason));
        }
    }

    /**
     * Never lets confidence alone decide anything: only reachable once
     * deterministic checks have already passed, and only acts when the
     * AI's own verdict agrees with the direction being thresholded (a
     * "rejected" opinion is never auto-approved just because some other
     * confidence number is high, and vice versa).
     */
    protected function applyThresholds(AiEvaluationResult $result): string
    {
        $approveThreshold = (float) (Setting::get('ai_auto_approve_threshold') ?: 0.85);
        $rejectThreshold = (float) (Setting::get('ai_auto_reject_threshold') ?: 0.85);

        if ($result->verdict === 'approved' && $result->confidence >= $approveThreshold) {
            return 'approved';
        }

        if ($result->verdict === 'rejected' && $result->confidence >= $rejectThreshold) {
            return 'rejected';
        }

        return 'needs_review';
    }

    protected function rejectionReason(): ?RejectionReason
    {
        $id = Setting::get('ai_rejection_reason_id');

        return $id ? RejectionReason::find($id) : null;
    }

    /** @param CheckResult[] $results */
    protected function serializeChecks(array $results): array
    {
        return array_map(fn (CheckResult $r) => $r->toArray(), $results);
    }

    protected function recordAudit(CampaignSubmission $submission, array $attributes): void
    {
        SubmissionVerification::create(array_merge([
            'campaign_submission_id' => $submission->id,
            'verifier' => 'ai',
            'verifier_label' => 'openai:' . (Setting::get('openai_model') ?: config('services.openai.model', 'gpt-4o-mini')),
        ], $attributes));
    }

    /** @param string[] $advisoryNotes */
    protected function buildEvaluationRequest(CampaignSubmission $submission, array $advisoryNotes): SubmissionEvaluationRequest
    {
        $campaign = $submission->campaign;

        $businessInfo = $campaign->requirementAnswers
            ->mapWithKeys(fn ($answer) => [$answer->field->label ?? "Field #{$answer->category_requirement_field_id}" => $answer->value])
            ->all();

        $evidenceItems = [];

        foreach ($campaign->category->requirementFields->where('fills_for', 'participant') as $field) {
            $evidenceItems[] = $this->evidenceItem($field->label, $field->type, (bool) $field->is_required, data_get($submission->answers, (string) $field->id));
        }

        foreach ($campaign->customFields as $field) {
            $evidenceItems[] = $this->evidenceItem($field->label, $field->type, (bool) $field->is_required, data_get($submission->answers, $field->field_key));
        }

        return new SubmissionEvaluationRequest(
            campaignTitle: $campaign->title,
            campaignDescription: $campaign->description,
            steps: $campaign->steps ?? [],
            businessProvidedInfo: $businessInfo,
            evidenceItems: $evidenceItems,
            advisoryNotes: $advisoryNotes,
        );
    }

    protected function evidenceItem(string $label, string $type, bool $isRequired, mixed $value): array
    {
        $item = ['label' => $label, 'type' => $type, 'is_required' => $isRequired];

        if ($value === null || $value === '') {
            $item['note'] = 'No value was submitted for this field.';

            return $item;
        }

        if ($type === 'file') {
            return array_merge($item, $this->fileEvidence((string) $value));
        }

        $item['text_value'] = (string) $value;

        return $item;
    }

    protected function fileEvidence(string $path): array
    {
        if (! Storage::disk('public')->exists($path)) {
            return ['note' => 'File evidence was recorded but could not be found on disk.'];
        }

        $mime = Storage::disk('public')->mimeType($path);

        if ($mime && str_starts_with($mime, 'image/')) {
            try {
                $contents = Storage::disk('public')->get($path);

                return ['image_data_uri' => 'data:' . $mime . ';base64,' . base64_encode($contents)];
            } catch (\Throwable) {
                return ['note' => 'Image evidence could not be read from storage.'];
            }
        }

        // Video and other non-image evidence can't be visually analysed by
        // a vision-capable chat model - noted for the AI so it doesn't
        // assume the file was inspected, rather than silently skipping it.
        return ['note' => 'A non-image file was uploaded (' . ($mime ?? 'unknown type') . ') - its content was not visually analysed.'];
    }
}
