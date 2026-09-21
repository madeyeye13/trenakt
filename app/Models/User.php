<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function emailVerificationCodes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmailVerificationCode::class);
    }

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    public function activeMode(): string
    {
        return $this->active_mode ?? $this->roles->first()?->name ?? 'participant';
    }

    public function hasBothModes(): bool
    {
        return $this->hasRole('participant') && $this->hasRole('business');
    }

    public function otherMode(): ?string
    {
        if (! $this->hasBothModes()) {
            return null;
        }

        return $this->activeMode() === 'participant' ? 'business' : 'participant';
    }
}