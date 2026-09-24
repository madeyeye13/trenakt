<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'campaign_submission_id', 'verifier', 'verifier_label', 'verdict', 'ai_verdict',
    'confidence', 'deterministic_checks', 'ai_checks', 'reasons', 'evidence_considered',
    'raw_response', 'error',
])]
class SubmissionVerification extends Model
{
    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'deterministic_checks' => 'array',
            'ai_checks' => 'array',
            'reasons' => 'array',
            'evidence_considered' => 'array',
            'raw_response' => 'array',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(CampaignSubmission::class, 'campaign_submission_id');
    }
}
