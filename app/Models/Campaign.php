<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id', 'campaign_category_id', 'title', 'description', 'steps',
    'rate_per_participant', 'target_participants', 'total_budget',
    'platform_fee_amount', 'status', 'rejection_reason',
    'submitted_at', 'reviewed_at', 'reviewed_by', 'completed_at',
    'allow_admin_edit', 'admin_edited_at', 'admin_edited_by', 'original_content',
    'task_mode', 'platforms', 'post_content_text', 'post_content_media', 'platform_source_links',
])]
class Campaign extends Model
{
    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'rate_per_participant' => 'decimal:2',
            'total_budget' => 'decimal:2',
            'platform_fee_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
            'allow_admin_edit' => 'boolean',
            'admin_edited_at' => 'datetime',
            'original_content' => 'array',
            'platforms' => 'array',
            'platform_source_links' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CampaignCategory::class, 'campaign_category_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * The admin who last saved an edit to this campaign's wording (see
     * admin_edited_at / original_content). Only ever set when the business
     * opted in via allow_admin_edit - see the migration that adds these
     * columns.
     */
    public function adminEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_edited_by');
    }

    public function requirementAnswers(): HasMany
    {
        return $this->hasMany(CampaignRequirementAnswer::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(CampaignSubmission::class);
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(CampaignCustomField::class)->orderBy('sort_order');
    }

    public function targeting(): HasOne
    {
        return $this->hasOne(CampaignTargeting::class);
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(CampaignImpression::class);
    }
}
