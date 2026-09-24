<?php

namespace App\Services\Verification\Checks;

use App\Models\CampaignSubmission;

/**
 * Format-only validation (not a reachability check - fetching an arbitrary
 * participant-submitted URL from the verification job would add an external
 * dependency and a timeout risk for a check that's supposed to be cheap and
 * instant). Blocking: a required link field that isn't a well-formed URL at
 * all is a factual problem the AI shouldn't be asked to interpret around.
 */
class UrlFieldsValidCheck implements VerificationCheck
{
    public function run(CampaignSubmission $submission): CheckResult
    {
        $invalid = [];

        foreach ($this->urlFields($submission) as $key => $label) {
            $value = data_get($submission->answers, $key);

            if ($value !== null && $value !== '' && filter_var($value, FILTER_VALIDATE_URL) === false) {
                $invalid[] = $label;
            }
        }

        if ($invalid !== []) {
            return new CheckResult(
                name: 'url_fields_valid',
                passed: false,
                severity: 'blocking',
                detail: 'Not a valid URL: ' . implode(', ', $invalid),
            );
        }

        return new CheckResult('url_fields_valid', true, 'blocking', 'All submitted URL fields are well-formed.');
    }

    /** @return array<string, string> field key => label */
    protected function urlFields(CampaignSubmission $submission): array
    {
        $fields = [];

        foreach ($submission->campaign->category->requirementFields->where('fills_for', 'participant')->where('type', 'url') as $field) {
            $fields[(string) $field->id] = $field->label;
        }

        foreach ($submission->campaign->customFields->where('type', 'url') as $field) {
            $fields[$field->field_key] = $field->label;
        }

        return $fields;
    }
}
