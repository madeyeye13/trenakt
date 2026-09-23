<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Formats a Naira amount for display in the viewing participant's own
 * currency. Everything is still actually charged, earned, and stored in
 * Naira - ActivationFeeService, ParticipantWalletService, ReferralService,
 * and the admin-configured settings all stay Naira-only. This only changes
 * what's shown on screen, converting with the rates ExchangeRateService
 * keeps refreshed. Display-only by design (see the session's earlier
 * decision): a participant outside Nigeria still pays in Naira at
 * checkout, they just see the equivalent in their own currency everywhere
 * else in the app.
 */
class CurrencyService
{
    protected array $symbols = [
        'NGN' => '₦',
        'GHS' => '₵',
        'USD' => '$',
        'GBP' => '£',
        'EUR' => '€',
        'KES' => 'KSh',
        'ZAR' => 'R',
    ];

    public function __construct(protected ExchangeRateService $rates)
    {
    }

    /**
     * Converts and formats a Naira amount for $user's own currency, falling
     * back to Naira for a guest, a user with no country set, or one whose
     * country's currency already is Naira. Defaults to the authenticated
     * user when none is given.
     */
    public function format(float $nairaAmount, ?User $user = null): string
    {
        $user ??= Auth::user();
        $currency = $user?->country?->currency_code ?? 'NGN';

        $amount = $currency === 'NGN' ? $nairaAmount : $this->rates->convert($nairaAmount, $currency);
        $symbol = $this->symbols[$currency] ?? ($currency . ' ');

        return $symbol . number_format($amount, 2);
    }
}
