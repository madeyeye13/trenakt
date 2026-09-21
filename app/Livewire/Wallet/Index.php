<?php

namespace App\Livewire\Wallet;

use App\Models\BusinessWallet;
use App\Models\WalletTransaction;
use App\Services\Payments\FlutterwaveService;
use App\Services\Payments\PaystackService;
use App\Services\Payments\WalletFundingService;
use App\Services\WalletService;
use Livewire\Component;

class Index extends Component
{
    public float $amount = 5000;
    public string $gateway = 'paystack';

    public function mount(): void
    {
        if (session()->has('toast')) {
            $toast = session('toast');
            $this->dispatch('toast', type: $toast['type'], message: $toast['message']);
        }
    }

    public function selectGateway(string $gateway): void
    {
        $this->gateway = $gateway;
    }

    public function fund(WalletFundingService $fundingService, PaystackService $paystack, FlutterwaveService $flutterwave)
    {
        $this->validate([
            'amount' => ['required', 'numeric', 'min:100'],
            'gateway' => ['required', 'in:paystack,flutterwave'],
        ]);

        $fundingRequest = $fundingService->initiate(auth()->id(), $this->gateway, $this->amount);
        $callbackUrl = route('wallet.callback', ['gateway' => $this->gateway]);

        try {
            $init = $this->gateway === 'paystack'
                ? $paystack->initialize(auth()->user()->email, $this->amount, $callbackUrl, $fundingRequest->reference)
                : $flutterwave->initialize(auth()->user()->email, $this->amount, $callbackUrl, $fundingRequest->reference);
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Could not connect to the payment gateway. Please try again.');
            return;
        }

        if (empty($init['authorization_url'])) {
            $this->dispatch('toast', type: 'error', message: 'The payment gateway did not return a payment link. Please try again.');
            return;
        }

        return redirect()->away($init['authorization_url']);
    }

    public function render(WalletService $wallet)
    {
        return view('livewire.wallet.index', [
            'balance' => $wallet->availableBalance(auth()->user()),
            'reserved' => BusinessWallet::where('user_id', auth()->id())->value('reserved_balance') ?? 0,
            'transactions' => WalletTransaction::where('user_id', auth()->id())->latest()->limit(20)->get(),
        ])->layout('components.layouts.app', ['title' => 'Wallet']);
    }
}