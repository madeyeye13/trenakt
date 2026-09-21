<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['campaign_id', 'label', 'field_key', 'type', 'is_required', 'sort_order'])]
class CampaignCustomField extends Model
{
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}