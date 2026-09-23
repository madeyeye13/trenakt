<?php

namespace App\Http\Controllers;

use App\Services\Payments\ActivationFeeService;
use App\Services\Payments\PaystackService;
use Illuminate\Http\Request;

class ActivationCallbackController extends Controller
{
    public function handle(Request $request, PaystackService $paystack, ActivationFeeService $activationFee)
    {
        $returnTo = session()->pull('activation_return_to', route('tasks.discover'));
        $reference = $request->query('reference');

        if (! $reference) {
            return redirect($returnTo)->with('toast', ['type' => 'error', 'message' => 'We could not find that payment.']);
        }

        $result = $paystack->verify($reference);
        $payment = $activationFee->complete($reference, $result['successful'], $result['amount']);

        if (! $payment) {
            return redirect($returnTo)->with('toast', ['type' => 'error', 'message' => 'We could not find that payment.']);
        }

        if ($payment->status === 'successful') {
            return redirect($returnTo)->with('toast', ['type' => 'success', 'message' => 'Account activated! You can now accept and submit tasks.']);
        }

        return redirect($returnTo)->with('toast', ['type' => 'error', 'message' => 'Payment could not be verified. If you were charged, it will reflect shortly, or contact support.']);
    }
}
