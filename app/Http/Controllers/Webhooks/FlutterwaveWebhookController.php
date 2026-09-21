<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\WalletFundingRequest;
use App\Services\Payments\WalletFundingService;
use Illuminate\Http\Request;

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request, WalletFundingService $fundingService)
    {
        $signature = $request->header('verif-hash');
        $expected = config('payments.flutterwave.webhook_secret');

        if (! $signature || ! $expected || ! hash_equals((string) $expected, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $data = $request->input('data', []);

        if (($data['status'] ?? null) === 'successful') {
            $reference = $data['tx_ref'] ?? null;
            $fundingRequest = $reference ? WalletFundingRequest::where('reference', $reference)->first() : null;

            if ($fundingRequest) {
                $fundingService->complete($fundingRequest, true, (float) ($data['amount'] ?? 0), $data['id'] ?? null);
            }
        }

        return response()->json(['message' => 'ok']);
    }
}