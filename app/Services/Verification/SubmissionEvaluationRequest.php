<?php

namespace App\Services\Verification;

/**
 * Everything an AiEvaluator needs to judge one submission, built generically
 * from the campaign's own steps/requirements/answers - never from a
 * category-specific enum. The same shape covers app testing, market
 * research, publicity, and any future task type, because it's built purely
 * from what the business and participant actually entered.
 */
final class SubmissionEvaluationRequest
{
    /**
     * @param string[] $steps plain-text instructions the business wrote
     * @param array<string, string> $businessProvidedInfo label => value, from the business's own campaign-creation answers
     * @param array<int, array{label: string, type: string, is_required: bool, text_value?: string, image_data_uri?: string, note?: string}> $evidenceItems
     * @param string[] $advisoryNotes deterministic-check notes to weigh, never conclusive on their own
     */
    public function __construct(
        public readonly string $campaignTitle,
        public readonly ?string $campaignDescription,
        public readonly array $steps,
        public readonly array $businessProvidedInfo,
        public readonly array $evidenceItems,
        public readonly array $advisoryNotes,
    ) {
    }
}
