<?php

namespace App\Http\Controllers;

use App\Models\WalletFundingRequest;
use App\Services\Payments\FlutterwaveService;
use App\Services\Payments\PaystackService;
use App\Services\Payments\WalletFundingService;
use Illuminate\Http\Request;

class WalletCallbackController extends Controller
{
    public function handle(Request $request, string $gateway, PaystackService $paystack, FlutterwaveService $flutterwave, WalletFundingService $fundingService)
    {
        $returnTo = session()->pull('wallet_return_to', route('wallet.index'));

        if ($gateway === 'paystack') {
            $reference = $request->query('reference');
            $fundingRequest = $reference ? WalletFundingRequest::where('reference', $reference)->first() : null;

            if ($fundingRequest) {
                $result = $paystack->verify($reference);
                $fundingService->complete($fundingRequest, $result['successful'], $result['amount']);
            }
        } elseif ($gateway === 'flutterwave') {
            $reference = $request->query('tx_ref');
            $transactionId = $request->query('transaction_id');
            $fundingRequest = $reference ? WalletFundingRequest::where('reference', $reference)->first() : null;

            if ($fundingRequest && $transactionId) {
                $result = $flutterwave->verify($reference, $transactionId);
                $fundingService->complete($fundingRequest, $result['successful'], $result['amount'], $transactionId);
            }
        }

        if (! isset($fundingRequest) || ! $fundingRequest) {
            return redirect($returnTo)->with('toast', ['type' => 'error', 'message' => 'We could not find that payment.']);
        }

        $fundingRequest->refresh();

        if ($fundingRequest->status === 'successful') {
            return redirect($returnTo)->with('toast', ['type' => 'success', 'message' => 'Wallet funded successfully.']);
        }

        return redirect($returnTo)->with('toast', ['type' => 'error', 'message' => 'Payment could not be verified. If you were charged, it will reflect shortly, or contact support.']);
    }
}