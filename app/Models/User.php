<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

// NOTE: 'country_id' was missing from this list, which meant
// User::create(['country_id' => ...]) in RegisterForm was silently
// dropping it on every registration (Eloquent ignores non-fillable
// attributes passed to create()/fill() unless strict mode is on, which
// this app doesn't enable). Added here as part of this change since the
// referral/activation-fee work touches the same create() call.
#[Fillable(['name', 'email', 'password', 'phone', 'date_of_birth', 'country_id'])]
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
            'date_of_birth' => 'date',
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

    public function participantWallet(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ParticipantWallet::class);
    }

    public function bankAccount(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ParticipantBankAccount::class);
    }

    public function submissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CampaignSubmission::class, 'participant_id');
    }

    public function withdrawalRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function activationPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivationPayment::class);
    }

    /**
     * The participant who referred this user at registration, if any.
     * Only ever set for participants, and only ever captured while the
     * referral system was switched on (see ReferralService).
     */
    public function referredBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    /**
     * Everyone this participant has referred, whether or not each one has
     * paid their activation fee yet.
     */
    public function referrals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class, 'referred_by_user_id');
    }

    public function age(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /**
     * The basics needed before a participant can browse or accept tasks:
     * enough to judge campaign eligibility (age, country via existing
     * country_id). Bank details are deliberately NOT required here - those
     * are only asked for at withdrawal time.
     */
    public function hasCompleteParticipantProfile(): bool
    {
        return filled($this->name)
            && filled($this->phone)
            && $this->date_of_birth !== null
            && $this->country_id !== null;
    }

    public function isEligibleForWithdrawal(): bool
    {
        return $this->hasCompleteParticipantProfile()
            && $this->country?->supportsPayout() === true
            && $this->bankAccount?->isVerified() === true;
    }

    public function hasPaidActivationFee(): bool
    {
        return $this->activationPayments()->where('status', 'successful')->exists();
    }

    /**
     * The code this participant shares to refer others. Generated lazily on
     * first access rather than at registration time, so it also works for
     * participants who existed before the referral system did.
     */
    public function referralCode(): string
    {
        if ($this->referral_code) {
            return $this->referral_code;
        }

        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        $this->forceFill(['referral_code' => $code])->save();

        return $code;
    }
}
