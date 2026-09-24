<?php

namespace App\Services\Verification;

/**
 * The abstraction between VerifySubmissionWithAi and whichever AI vendor
 * actually judges evidence. OpenAiEvaluator is the only implementation
 * today; swapping providers later, or adding a second one to compare
 * against, is a matter of writing a new class and rebinding this interface
 * in AppServiceProvider - nothing about prompts, thresholds, or the
 * decision logic in the job needs to change.
 *
 * @throws AiEvaluationException on any failure - a missing/invalid key, a
 *         network or timeout error, or a response that doesn't match the
 *         required structured shape. Implementations must never let a raw
 *         HTTP or JSON exception escape this method uncaught.
 */
interface AiEvaluator
{
    public function evaluate(SubmissionEvaluationRequest $request): AiEvaluationResult;
}
