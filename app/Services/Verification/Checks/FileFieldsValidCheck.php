<?php

namespace App\Services\Verification\Checks;

use App\Models\CampaignSubmission;
use Illuminate\Support\Facades\Storage;

/**
 * Confirms a submitted file field actually exists on the 'public' disk (the
 * same disk Discover::submit() stores uploads on) and isn't a zero-byte
 * file. Whether it's required at all is RequiredEvidencePresentCheck's job;
 * this only runs against values that were actually submitted. Blocking: a
 * file reference that doesn't resolve to real evidence is a factual
 * problem, not something for the AI to guess around.
 */
class FileFieldsValidCheck implements VerificationCheck
{
    public function run(CampaignSubmission $submission): CheckResult
    {
        $broken = [];

        foreach ($this->fileFields($submission) as $key => $label) {
            $value = data_get($submission->answers, $key);

            if ($value === null || $value === '') {
                continue;
            }

            if (! Storage::disk('public')->exists($value) || Storage::disk('public')->size($value) === 0) {
                $broken[] = $label;
            }
        }

        if ($broken !== []) {
            return new CheckResult(
                name: 'file_fields_valid',
                passed: false,
                severity: 'blocking',
                detail: 'File evidence missing or empty on disk: ' . implode(', ', $broken),
            );
        }

        return new CheckResult('file_fields_valid', true, 'blocking', 'All submitted file evidence exists and is non-empty.');
    }

    /** @return array<string, string> field key => label */
    protected function fileFields(CampaignSubmission $submission): array
    {
        $fields = [];

        foreach ($submission->campaign->category->requirementFields->where('fills_for', 'participant')->where('type', 'file') as $field) {
            $fields[(string) $field->id] = $field->label;
        }

        foreach ($submission->campaign->customFields->where('type', 'file') as $field) {
            $fields[$field->field_key] = $field->label;
        }

        return $fields;
    }
}
