<?php

namespace App\Services\Verification;

use App\Models\CampaignSubmission;
use App\Services\Verification\Checks\CheckResult;
use App\Services\Verification\Checks\DuplicateAnswerCheck;
use App\Services\Verification\Checks\FileFieldsValidCheck;
use App\Services\Verification\Checks\RequiredEvidencePresentCheck;
use App\Services\Verification\Checks\UrlFieldsValidCheck;

/**
 * Runs every deterministic check against a submission and answers the one
 * question that actually matters before AI is ever involved: is there a
 * factual, non-interpretive problem here that should block auto-approval
 * outright? See Checks\CheckResult for what 'blocking' vs 'advisory' means.
 */
class DeterministicVerifier
{
    /** @return CheckResult[] */
    public function run(CampaignSubmission $submission): array
    {
        $checks = [
            new RequiredEvidencePresentCheck(),
            new UrlFieldsValidCheck(),
            new FileFieldsValidCheck(),
            new DuplicateAnswerCheck(),
        ];

        return array_map(fn ($check) => $check->run($submission), $checks);
    }

    /** @param CheckResult[] $results */
    public function hasBlockingFailure(array $results): bool
    {
        foreach ($results as $result) {
            if ($result->severity === 'blocking' && ! $result->passed) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CheckResult[] $results
     * @return string[] human-readable notes from failed advisory checks only,
     *                   meant as context the AI should weigh, not a verdict.
     */
    public function advisoryNotes(array $results): array
    {
        return array_values(array_map(
            fn (CheckResult $r) => $r->detail,
            array_filter($results, fn (CheckResult $r) => $r->severity === 'advisory' && ! $r->passed),
        ));
    }
}
