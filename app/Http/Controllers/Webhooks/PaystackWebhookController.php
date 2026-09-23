<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\WalletFundingRequest;
use App\Services\Payments\ActivationFeeService;
use App\Services\Payments\WalletFundingService;
use Illuminate\Http\Request;

class PaystackWebhookController extends Controller
{
    public function handle(Request $request, WalletFundingService $fundingService, ActivationFeeService $activationFee)
    {
        $signature = $request->header('X-Paystack-Signature');
        $expected = hash_hmac('sha512', $request->getContent(), (string) config('payments.paystack.secret_key'));

        if (! $signature || ! hash_equals($expected, $signature)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        if ($request->input('event') === 'charge.success') {
            $reference = $request->input('data.reference');
            $amount = ($request->input('data.amount') ?? 0) / 100;

            // Activation-fee references are always 'act_...' (see
            // ActivationFeeService::initialize()), which keeps them from
            // ever being confused with a 'trk_...' wallet-funding reference
            // - no need to query both tables to figure out which this is.
            if ($reference && str_starts_with($reference, 'act_')) {
                $activationFee->complete($reference, true, $amount);
            } elseif ($reference) {
                $fundingRequest = WalletFundingRequest::where('reference', $reference)->first();

                if ($fundingRequest) {
                    $fundingService->complete($fundingRequest, true, $amount);
                }
            }
        }

        return response()->json(['message' => 'ok']);
    }
}
