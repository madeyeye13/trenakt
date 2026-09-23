<?php

namespace App\Services\Payments;

use App\Models\Country;
use GuzzleHttp\Client;


class PaystackService
{
    protected Client $client;

    /**
     * secretKey/countrySlug/currency default to the platform's original
     * Nigeria setup (the .env PAYSTACK_SECRET_KEY), so every existing
     * `PaystackService $x` type-hint anywhere in the app keeps working
     * exactly as before with zero changes at the call site. Only the
     * withdrawal path needs a different country's config, and it gets
     * that via forCountry() below instead of container auto-wiring.
     */
    public function __construct(
        protected ?string $secretKey = null,
        protected string $countrySlug = 'nigeria',
        protected string $currency = 'NGN',
    ) {
        $this->client = new Client([
            'base_uri' => config('payments.paystack.base_url'),
            'headers' => [
                'Authorization' => 'Bearer ' . ($this->secretKey ?? config('payments.paystack.secret_key')),
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Builds a PaystackService configured for a specific country's payout
     * account - its own secret key, its own gateway country slug, its own
     * currency. Used only for participant withdrawals; every other use of
     * PaystackService in the app (wallet funding, activation fees) keeps
     * using the default Nigeria/.env instance via normal DI.
     */
    public static function forCountry(Country $country): self
    {
        return new self(
            secretKey: $country->payoutSecretKeyResolved(),
            countrySlug: $country->payoutCountrySlug(),
            currency: $country->currency_code,
        );
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

    /**
     * Banks (or, for $type = 'mobile_money', mobile network operators) this
     * instance's country knows about, for the "select your bank" dropdown
     * in the withdrawal setup modal. Cached by the caller if needed - this
     * always hits the API.
     *
     * @return array<int, array{name: string, code: string}>
     */
    public function listBanks(string $type = 'bank'): array
    {
        $query = ['country' => $this->countrySlug, 'currency' => $this->currency];

        if ($type === 'mobile_money') {
            $query['type'] = 'mobile_money';
        }

        $response = $this->client->get('/bank', ['query' => $query]);

        $data = json_decode($response->getBody()->getContents(), true);

        return collect($data['data'] ?? [])
            ->map(fn (array $bank) => ['name' => $bank['name'], 'code' => $bank['code']])
            ->values()
            ->all();
    }

    /**
     * Resolves an account number + bank code to the account holder's name on
     * record at the bank (or mobile money wallet). This is what lets us
     * check a participant's typed profile name against their real account
     * name without ever collecting an ID.
     *
     * @return array{account_number: string, account_name: string}
     */
    public function resolveAccount(string $accountNumber, string $bankCode): array
    {
        $response = $this->client->get('/bank/resolve', [
            'query' => [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'account_number' => $data['data']['account_number'] ?? $accountNumber,
            'account_name' => $data['data']['account_name'] ?? null,
        ];
    }

    /**
     * Registers a transfer recipient with Paystack so we can pay them later.
     * Returns the recipient_code to store alongside the bank account.
     * $type is 'nuban' for a bank account or 'mobile_money' for a mobile
     * wallet.
     */
    public function createTransferRecipient(string $accountNumber, string $bankCode, string $accountName, string $type = 'nuban'): string
    {
        $response = $this->client->post('/transferrecipient', [
            'json' => [
                'type' => $type,
                'name' => $accountName,
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
                'currency' => $this->currency,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['data']['recipient_code'] ?? throw new \RuntimeException(
            'Paystack did not return a recipient_code: ' . $response->getBody()
        );
    }

    /**
     * Sends money to a previously-created transfer recipient, out of THIS
     * instance's account balance (i.e. whichever country's key it was
     * built with). Returns the gateway's transfer reference and status so
     * the caller can decide whether to treat it as paid, pending, or
     * failed.
     *
     * @return array{transfer_code: ?string, status: ?string, reference: string}
     */
    public function initiateTransfer(string $recipientCode, float $amount, string $reference, string $reason): array
    {
        $response = $this->client->post('/transfer', [
            'json' => [
                'source' => 'balance',
                'amount' => (int) round($amount * 100), // kobo / pesewas / cents
                'recipient' => $recipientCode,
                'reference' => $reference,
                'reason' => $reason,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return [
            'transfer_code' => $data['data']['transfer_code'] ?? null,
            'status' => $data['data']['status'] ?? null, // 'success', 'pending', 'otp', 'failed'
            'reference' => $reference,
        ];
    }
}
