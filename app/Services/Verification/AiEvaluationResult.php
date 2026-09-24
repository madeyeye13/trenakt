<?php

namespace App\Services\Verification;

/**
 * The AI's raw opinion on a submission, before any confidence threshold is
 * applied - see VerifySubmissionWithAi for how this becomes an actual
 * decision. Never trusted blindly: `verdict` here is what the AI thinks
 * should happen, not what will happen.
 */
final class AiEvaluationResult
{
    /**
     * @param array<int, array{name: string, passed: bool, detail: string}> $checks
     * @param string[] $reasons
     * @param string[] $evidenceConsidered
     * @param array $raw the full decoded API response, kept for audit/debugging
     */
    public function __construct(
        public readonly string $verdict,
        public readonly float $confidence,
        public readonly array $checks,
        public readonly array $reasons,
        public readonly array $evidenceConsidered,
        public readonly array $raw,
    ) {
    }
}
