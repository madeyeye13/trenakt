<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'iso_code', 'currency_code', 'is_active',
    'payout_enabled', 'payout_provider', 'payout_secret_key', 'payout_country_slug', 'payout_methods',
])]
class Country extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'payout_enabled' => 'boolean',
            // Stored encrypted - never plain text in the database, and
            // never re-rendered into an admin form once saved (see
            // Admin\Countries\Index).
            'payout_secret_key' => 'encrypted',
            'payout_methods' => 'array',
        ];
    }

    /**
     * The gateway secret key to actually use for this country's payouts.
     * Nigeria is the one country the app has always worked with out of the
     * box (via PAYSTACK_SECRET_KEY in .env), so it falls back to that key
     * when no country-specific one has been set - every other country
     * must have its own key entered on the Countries admin page before it
     * can pay anyone out.
     */
    public function payoutSecretKeyResolved(): ?string
    {
        if (filled($this->payout_secret_key)) {
            return $this->payout_secret_key;
        }

        if ($this->payout_provider === 'paystack' && $this->iso_code === 'NG') {
            return config('payments.paystack.secret_key');
        }

        return null;
    }

    /**
     * Whether withdrawals actually work for this country right now: the
     * admin has switched it on AND a usable secret key resolves for it.
     * A country can be "active" for registration (is_active) without
     * supporting payout yet - that's what lets people from a new country
     * sign up and earn before you've set up how to pay them.
     */
    public function supportsPayout(): bool
    {
        return $this->payout_enabled
            && filled($this->payout_provider)
            && filled($this->payoutSecretKeyResolved());
    }

    public function payoutCountrySlug(): string
    {
        return $this->payout_country_slug ?: strtolower($this->name);
    }

    /**
     * @return array<int, string> e.g. ['bank'] or ['bank', 'mobile_money']
     */
    public function payoutMethods(): array
    {
        return filled($this->payout_methods) ? $this->payout_methods : ['bank'];
    }
}
