<?php

namespace App\Services\Payments;

use App\Models\User;

/**
 * The one place that answers "can this participant withdraw, and through
 * which gateway account?" - so WithdrawalService and the Earnings page
 * never have to know that only Paystack exists today, or that Nigeria's
 * key comes from .env while every other country's comes from the
 * Countries admin page. Adding a new country is then just an admin-side
 * toggle + API key, never a code change; adding a genuinely new gateway
 * (not just a new Paystack-country key) is a small, contained change here
 * and in PaystackService's sibling classes.
 */
class CountryPayoutResolver
{
    public function isAvailableFor(User $participant): bool
    {
        return (bool) $participant->country?->supportsPayout();
    }

    public function gatewayFor(User $participant): ?PaystackService
    {
        $country = $participant->country;

        if (! $country?->supportsPayout()) {
            return null;
        }

        return match ($country->payout_provider) {
            'paystack' => PaystackService::forCountry($country),
            default => null,
        };
    }

    /**
     * @return array<int, string> e.g. ['bank'] or ['bank', 'mobile_money'] - empty if unsupported.
     */
    public function methodsFor(User $participant): array
    {
        if (! $this->isAvailableFor($participant)) {
            return [];
        }

        return $participant->country->payoutMethods();
    }
}
