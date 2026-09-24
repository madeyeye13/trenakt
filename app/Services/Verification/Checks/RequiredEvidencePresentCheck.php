<?php

namespace App\Services\Verification\Checks;

use App\Models\CampaignSubmission;

/**
 * Defense in depth: Discover::submit() already validates every required
 * field is filled before a submission can even be created, but this re-
 * checks it independently at verification time (a field's is_required could
 * change after submission, or this path could one day be reached some other
 * way). Blocking: a submission provably missing something the business
 * required is never auto-approved, no matter how confident the AI step is.
 */
class RequiredEvidencePresentCheck implements VerificationCheck
{
    public function run(CampaignSubmission $submission): CheckResult
    {
        $missing = [];

        foreach ($this->requiredFields($submission) as $key => $label) {
            $value = data_get($submission->answers, $key);

            if ($value === null || $value === '') {
                $missing[] = $label;
            }
        }

        if ($missing !== []) {
            return new CheckResult(
                name: 'required_evidence_present',
                passed: false,
                severity: 'blocking',
                detail: 'Missing required evidence: ' . implode(', ', $missing),
            );
        }

        return new CheckResult('required_evidence_present', true, 'blocking', 'All required fields have a submitted value.');
    }

    /** @return array<string, string> field key => label */
    protected function requiredFields(CampaignSubmission $submission): array
    {
        $fields = [];

        foreach ($submission->campaign->category->requirementFields->where('fills_for', 'participant')->where('is_required', true) as $field) {
            $fields[(string) $field->id] = $field->label;
        }

        foreach ($submission->campaign->customFields->where('is_required', true) as $field) {
            $fields[$field->field_key] = $field->label;
        }

        return $fields;
    }
}
