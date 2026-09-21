<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'min_rate', 'max_rate', 'platform_fee_percentage', 'min_participants', 'max_participants', 'is_active'])]
class CampaignCategory extends Model
{
    protected function casts(): array
    {
        return [
            'min_rate' => 'decimal:2',
            'max_rate' => 'decimal:2',
            'platform_fee_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function requirementFields(): HasMany
    {
        return $this->hasMany(CategoryRequirementField::class)->orderBy('sort_order');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }
}