<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    protected string $baseCurrency = 'NGN';

    public function refresh(): void
    {
        $apiKey = config('services.exchangerate.key');

        $response = Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/{$this->baseCurrency}");

        if (! $response->successful()) {
            report(new \Exception('Exchange rate API request failed: '.$response->body()));

            return;
        }

        $rates = $response->json('conversion_rates', []);

        foreach ($rates as $targetCurrency => $rate) {
            if ($targetCurrency === $this->baseCurrency) {
                continue;
            }

            ExchangeRate::updateOrCreate(
                ['base_currency' => $this->baseCurrency, 'target_currency' => $targetCurrency],
                ['rate' => $rate, 'fetched_at' => now()]
            );
        }
    }

    public function convert(float $amount, string $toCurrency): float
    {
        if ($toCurrency === $this->baseCurrency) {
            return $amount;
        }

        $rate = ExchangeRate::where('base_currency', $this->baseCurrency)
            ->where('target_currency', $toCurrency)
            ->value('rate');

        return $rate ? round($amount * $rate, 2) : $amount;
    }
}