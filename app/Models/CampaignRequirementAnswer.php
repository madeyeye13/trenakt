<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['campaign_id', 'category_requirement_field_id', 'value'])]
class CampaignRequirementAnswer extends Model
{
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(CategoryRequirementField::class, 'category_requirement_field_id');
    }
}