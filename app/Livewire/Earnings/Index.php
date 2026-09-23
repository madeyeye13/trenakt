<?php

namespace App\Livewire\Earnings;

use App\Models\ParticipantBankAccount;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Services\ParticipantWalletService;
use App\Services\Payments\ActivationFeeService;
use App\Services\Payments\CountryPayoutResolver;
use App\Services\Payments\PaystackService;
use App\Services\Payments\WithdrawalService;
use App\Services\ReferralService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{
    // Add-bank-account step
    public string $method = 'bank'; // 'bank' or 'mobile_money' - which withdrawal method this country offers
    public string $bankCode = '';
    public string $bankName = '';
    public string $accountNumber = '';
    public ?string $resolvedAccountName = null;
    public bool $nameMismatch = false;

    // Withdraw step
    public string $amount = '';

    public function updatedMethod(): void
    {
        // A bank code selected for one method (bank vs mobile money) isn't
        // valid for the other, so switching starts the picker over.
        $this->bankCode = '';
        $this->bankName = '';
        $this->accountNumber = '';
        $this->resolvedAccountName = null;
        $this->nameMismatch = false;
        $this->resetValidation();
    }

    public function openWithdraw(CountryPayoutResolver $payoutResolver): void
    {
        $user = Auth::user()->fresh(['bankAccount', 'country']);

        if (! $payoutResolver->isAvailableFor($user)) {
            $this->dispatch('toast', type: 'error', message: 'Withdrawals for your country aren\'t set up yet. Hang tight, we\'re working on it.');
            return;
        }

        if (! $user->bankAccount?->isVerified()) {
            $this->resetBankForm();
            $this->method = $payoutResolver->methodsFor($user)[0] ?? 'bank';
            $this->dispatch('open-modal', name: 'add-bank-account');
            return;
        }

        $this->amount = '';
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'request-withdrawal');
    }

    public function resolveAccount(CountryPayoutResolver $payoutResolver): void
    {
        $this->validate([
            'bankCode' => ['required', 'string'],
            // Format varies by country and method (Nigerian NUBAN is a
            // fixed 10 digits, but that's not true everywhere), so this is
            // deliberately loose - the gateway's own resolve call below is
            // the real check.
            'accountNumber' => ['required', 'string', 'min:4', 'max:20'],
        ]);

        $user = Auth::user();
        $gateway = $payoutResolver->gatewayFor($user);

        if (! $gateway) {
            $this->addError('accountNumber', 'Withdrawals for your country aren\'t set up yet.');
            return;
        }

        $this->resolvedAccountName = null;
        $this->nameMismatch = false;
        $this->bankName = collect($this->banks($gateway, $user->country_id))->firstWhere('code', $this->bankCode)['name'] ?? $this->bankCode;

        try {
            $result = $gateway->resolveAccount($this->accountNumber, $this->bankCode);
        } catch (\Throwable $e) {
            Log::error('Bank account resolution failed', ['message' => $e->getMessage(), 'user_id' => Auth::id()]);
            $this->addError('accountNumber', 'We could not verify this account number. Please double-check it and try again.');
            return;
        }

        if (! $result['account_name']) {
            $this->addError('accountNumber', 'We could not verify this account number. Please double-check it and try again.');
            return;
        }

        $this->resolvedAccountName = $result['account_name'];
        $this->nameMismatch = ! $this->namesRoughlyMatch(Auth::user()->name, $this->resolvedAccountName);
    }

    public function saveBankAccount(): void
    {
        if (! $this->resolvedAccountName || $this->nameMismatch) {
            return;
        }

        ParticipantBankAccount::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'type' => $this->method,
                'bank_code' => $this->bankCode,
                'bank_name' => $this->bankName,
                'network' => $this->method === 'mobile_money' ? $this->bankName : null,
                'account_number' => $this->accountNumber,
                'account_name' => $this->resolvedAccountName,
                'recipient_code' => null,
                'verified_at' => now(),
            ]
        );

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: $this->method === 'mobile_money' ? 'Mobile money account verified and saved.' : 'Bank account verified and saved.');
        $this->resetBankForm();

        $this->amount = '';
        $this->dispatch('open-modal', name: 'request-withdrawal');
    }

    protected function resetBankForm(): void
    {
        $this->bankCode = '';
        $this->bankName = '';
        $this->accountNumber = '';
        $this->resolvedAccountName = null;
        $this->nameMismatch = false;
        $this->resetValidation();
    }

    protected function namesRoughlyMatch(string $a, string $b): bool
    {
        $normalize = fn (string $s) => collect(preg_split('/\s+/', trim(mb_strtolower($s))))
            ->filter()
            ->sort()
            ->values();

        $left = $normalize($a);
        $right = $normalize($b);

        [$shorter, $longer] = $left->count() <= $right->count() ? [$left, $right] : [$right, $left];

        if ($shorter->isEmpty()) {
            return false;
        }

        return $shorter->every(fn ($word) => $longer->contains($word));
    }

    public function requestWithdrawal(WithdrawalService $withdrawals): void
    {
        $this->validate([
            'amount' => ['required', 'numeric', 'min:100'],
        ]);

        try {
            $withdrawals->requestWithdrawal(Auth::user(), (float) $this->amount);
        } catch (ValidationException $e) {
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: collect($e->errors())->flatten()->first() ?? 'Could not request this withdrawal.');
            return;
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Withdrawal requested. It will be paid out on the next payout day.');
        $this->amount = '';
    }

    /**
     * Banks (or mobile network operators, when $this->method is
     * mobile_money) for the participant's own country gateway. Cached per
     * country + method so Nigeria's list never bleeds into Ghana's.
     */
    protected function banks(PaystackService $gateway, ?int $countryId): array
    {
        return cache()->remember("paystack:banks:{$countryId}:{$this->method}", now()->addDay(), function () use ($gateway) {
            try {
                return $gateway->listBanks($this->method);
            } catch (\Throwable $e) {
                Log::error('Failed to fetch bank list', ['message' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function render(
        ParticipantWalletService $wallet,
        WithdrawalService $withdrawals,
        CountryPayoutResolver $payoutResolver,
        ActivationFeeService $activationFee,
        ReferralService $referrals,
    ) {
        $user = Auth::user()->fresh(['bankAccount', 'country']);
        $referralSystemEnabled = $activationFee->isEnabled();

        $payoutSupported = $payoutResolver->isAvailableFor($user);
        $payoutMethods = $payoutResolver->methodsFor($user);
        $gateway = $payoutSupported ? $payoutResolver->gatewayFor($user) : null;
        $banks = $gateway ? $this->banks($gateway, $user->country_id) : [];

        return view('livewire.earnings.index', [
            'availableBalance' => $wallet->availableBalance($user),
            'reservedBalance' => $wallet->walletFor($user)->reserved_balance,
            'referralEarnings' => WalletTransaction::where('user_id', $user->id)
                ->where('wallet_type', 'participant')
                ->where('type', 'referral_reward_earned')
                ->sum('amount'),
            'transactions' => WalletTransaction::where('user_id', $user->id)
                ->where('wallet_type', 'participant')
                ->latest()
                ->limit(10)
                ->get(),
            'pendingWithdrawals' => $user->withdrawalRequests()->whereIn('status', ['pending', 'processing'])->latest()->get(),
            'payoutWindowOpen' => $withdrawals->isPayoutWindowOpen(),
            'payoutDay' => ucfirst((string) Setting::get('payout_day', 'thursday')),
            'profileComplete' => $user->hasCompleteParticipantProfile(),
            'payoutSupported' => $payoutSupported,
            'payoutMethods' => $payoutMethods,
            'banks' => $banks,
            'referralSystemEnabled' => $referralSystemEnabled,
            'referralLink' => $referralSystemEnabled ? $referrals->linkFor($user) : null,
            'referralCode' => $referralSystemEnabled ? $user->referralCode() : null,
            'referrals' => $referralSystemEnabled
                ? $user->referrals()->with(['activationPayments' => fn ($q) => $q->where('status', 'successful')])->latest()->limit(6)->get()
                : collect(),
        ])->layout('components.layouts.app', ['title' => 'Earnings']);
    }
}
