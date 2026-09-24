<?php

namespace App\Services\Verification\Checks;

/**
 * The outcome of one deterministic check. `severity` decides how much
 * weight a failure carries: a 'blocking' failure is a factual problem (a
 * required field genuinely empty, a URL that isn't a URL) that prevents
 * auto-approval outright, regardless of how confident the AI step would
 * otherwise be. An 'advisory' failure (e.g. a possible duplicate answer)
 * is only ever passed to the AI as context to weigh, never a hard stop on
 * its own.
 */
final class CheckResult
{
    public function __construct(
        public readonly string $name,
        public readonly bool $passed,
        public readonly string $severity,
        public readonly string $detail,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'passed' => $this->passed,
            'severity' => $this->severity,
            'detail' => $this->detail,
        ];
    }
}
