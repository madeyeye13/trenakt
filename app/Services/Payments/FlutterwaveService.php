<?php

namespace App\Services\Payments;

use GuzzleHttp\Client;


class FlutterwaveService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('payments.flutterwave.base_url'),
            'headers' => [
                'Authorization' => 'Bearer ' . config('payments.flutterwave.secret_key'),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function initialize(string $email, float $amountNaira, string $callbackUrl, string $reference): array
    {
        $response = $this->client->post('/payments', [
            'json' => [
                'tx_ref' => $reference,
                'amount' => $amountNaira,
                'currency' => 'NGN',
                'redirect_url' => $callbackUrl,
                'customer' => ['email' => $email],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'reference' => $reference,
            'authorization_url' => $data['data']['link'] ?? null,
        ];
    }

    public function verify(string $reference, string $transactionId): array
    {
        $response = $this->client->get("/transactions/{$transactionId}/verify");
        $data = json_decode($response->getBody()->getContents(), true);

        $status = $data['data']['status'] ?? null;
        $amount = $data['data']['amount'] ?? 0;
        $txRef = $data['data']['tx_ref'] ?? null;

        return [
            'successful' => $status === 'successful' && $txRef === $reference,
            'amount' => $amount,
            'reference' => $reference,
            'raw' => $data,
        ];
    }
}