<?php

namespace App\Services\Verification\Checks;

use App\Models\CampaignSubmission;
use Illuminate\Support\Collection;

/**
 * Flags free-text answers that match another participant's submission on
 * the same campaign word-for-word. Deliberately advisory, not blocking: two
 * honest participants can legitimately give similar short answers, so this
 * is only ever handed to the AI as context to weigh alongside everything
 * else, never a reason by itself to withhold approval.
 */
class DuplicateAnswerCheck implements VerificationCheck
{
    /** Short values are too likely to coincide by chance to be meaningful. */
    protected const MIN_LENGTH = 15;

    public function run(CampaignSubmission $submission): CheckResult
    {
        $ownAnswers = $this->comparableAnswers($submission->answers);

        if ($ownAnswers->isEmpty()) {
            return new CheckResult('duplicate_answers', true, 'advisory', 'No free-text answers long enough to compare.');
        }

        $duplicateFound = CampaignSubmission::where('campaign_id', $submission->campaign_id)
            ->where('id', '!=', $submission->id)
            ->pluck('answers')
            ->contains(fn ($otherAnswers) => $ownAnswers->intersect($this->comparableAnswers($otherAnswers))->isNotEmpty());

        if ($duplicateFound) {
            return new CheckResult(
                name: 'duplicate_answers',
                passed: false,
                severity: 'advisory',
                detail: "One or more of this participant's free-text answers match another participant's submission on this campaign word-for-word.",
            );
        }

        return new CheckResult('duplicate_answers', true, 'advisory', 'No exact-match duplicate answers found among other submissions on this campaign.');
    }

    protected function comparableAnswers(mixed $answers): Collection
    {
        return collect(is_array($answers) ? $answers : [])
            ->filter(fn ($value) => is_string($value) && mb_strlen(trim($value)) >= self::MIN_LENGTH)
            ->map(fn ($value) => trim(mb_strtolower($value)))
            ->values();
    }
}
