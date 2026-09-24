<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['campaign_id', 'participant_id', 'status', 'answers', 'submitted_at', 'reviewed_at', 'rejection_reason', 'rejection_reason_id', 'reward_revoked_at', 'reward_revocation_reason', 'revoked_by'])]
class CampaignSubmission extends Model
{
    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'reward_revoked_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function rejectionReason(): BelongsTo
    {
        return $this->belongsTo(RejectionReason::class);
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Every AI verification attempt made on this submission, most recent
     * first. Human review doesn't write rows here - see
     * SubmissionVerification's migration docblock.
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(SubmissionVerification::class)->latest();
    }

    public function latestVerification(): HasOne
    {
        return $this->hasOne(SubmissionVerification::class)->latestOfMany();
    }
}
