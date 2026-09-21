<?php

namespace App\Services\Payments;

use GuzzleHttp\Client;


class PaystackService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('payments.paystack.base_url'),
            'headers' => [
                'Authorization' => 'Bearer ' . config('payments.paystack.secret_key'),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function initialize(string $email, float $amountNaira, string $callbackUrl, string $reference): array
    {
        $response = $this->client->post('/transaction/initialize', [
            'json' => [
                'email' => $email,
                'amount' => (int) round($amountNaira * 100), // kobo
                'reference' => $reference,
                'callback_url' => $callbackUrl,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'reference' => $reference,
            'authorization_url' => $data['data']['authorization_url'] ?? null,
        ];
    }

    public function verify(string $reference): array
    {
        $response = $this->client->get("/transaction/verify/{$reference}");
        $data = json_decode($response->getBody()->getContents(), true);

        $status = $data['data']['status'] ?? null;
        $amountKobo = $data['data']['amount'] ?? 0;

        return [
            'successful' => $status === 'success',
            'amount' => $amountKobo / 100,
            'reference' => $reference,
            'raw' => $data,
        ];
    }
}