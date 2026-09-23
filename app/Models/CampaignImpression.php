<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['campaign_id', 'participant_id', 'first_seen_at', 'detail_opened_at'])]
class CampaignImpression extends Model
{
    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'detail_opened_at' => 'datetime',
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
}
