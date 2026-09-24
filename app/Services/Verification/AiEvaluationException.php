<?php

namespace App\Services\Verification;

/**
 * Thrown by any AiEvaluator implementation for every failure mode - missing
 * or invalid key, network/timeout error, a non-2xx response, or a response
 * that doesn't match the required structured shape. Callers (currently only
 * VerifySubmissionWithAi) treat every instance of this identically: log it,
 * record it on the audit row, and leave the submission at 'submitted' for a
 * human. Nothing ever tries to interpret *which* failure this was in order
 * to take a different action - that would risk one failure mode being
 * handled less safely than another.
 */
class AiEvaluationException extends \RuntimeException
{
}
