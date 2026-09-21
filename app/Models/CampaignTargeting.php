<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['campaign_id', 'country_ids', 'min_age', 'max_age', 'gender'])]
class CampaignTargeting extends Model
{
    protected $table = 'campaign_targeting';
    protected function casts(): array
    {
        return [
            'country_ids' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function countries()
    {
        return Country::whereIn('id', $this->country_ids ?? [])->get();
    }
}