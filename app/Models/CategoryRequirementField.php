<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['campaign_category_id', 'label', 'field_key', 'type', 'is_required', 'sort_order'])]
class CategoryRequirementField extends Model
{
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CampaignCategory::class, 'campaign_category_id');
    }
}