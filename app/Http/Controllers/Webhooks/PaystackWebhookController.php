<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\WalletFundingRequest;
use App\Services\Payments\WalletFundingService;
use Illuminate\Http\Request;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request, WalletFundingService $fundingService)
    {
        $signature = $request->header('X-Paystack-Signature');
        $expected = hash_hmac('sha512', $request->getContent(), (string) config('payments.paystack.secret_key'));

        if (! $signature || ! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        if ($request->input('event') === 'charge.success') {
            $reference = $request->input('data.reference');
            $fundingRequest = $reference ? WalletFundingRequest::where('reference', $reference)->first() : null;

            if ($fundingRequest) {
                $amount = ($request->input('data.amount') ?? 0) / 100;
                $fundingService->complete($fundingRequest, true, $amount);
            }
        }

        return response()->json(['message' => 'ok']);
    }
}