<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Component;

class Index extends Component
{
    public string $payoutDay = 'thursday';
    public bool $withdrawalAlwaysOpen = false;
    public string $verificationMode = 'human';
    public bool $activationFeeEnabled = false;
    public string $activationFeeAmount = '';
    public bool $activationFeeGrandfatherExisting = true;
    public string $referralRewardAmount = '';

    public function mount(): void
    {
        $this->payoutDay = (string) Setting::get('payout_day', 'thursday');
        $this->withdrawalAlwaysOpen = (bool) Setting::get('withdrawal_always_open', false);
        $this->verificationMode = (string) Setting::get('verification_mode', 'human');
        $this->activationFeeEnabled = (bool) Setting::get('activation_fee_enabled', false);
        $this->activationFeeAmount = (string) Setting::get('activation_fee_amount', '');
        $this->activationFeeGrandfatherExisting = (bool) Setting::get('activation_fee_grandfather_existing', true);
        $this->referralRewardAmount = (string) Setting::get('referral_reward_amount', '');
    }

    public function save(): void
    {
        $this->validate([
            'payoutDay' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'activationFeeAmount' => [$this->activationFeeEnabled ? 'required' : 'nullable', 'numeric', 'min:0'],
            'referralRewardAmount' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'activationFeeAmount' => 'activation fee amount',
            'referralRewardAmount' => 'referral reward amount',
        ]);

        Setting::set('payout_day', $this->payoutDay);
        // The value column is plain text, so store an explicit '1'/'0' rather
        // than a PHP bool (Postgres rejects binding a boolean into a text
        // column). PHP treats the string '0' as falsy, so reads still work
        // with a plain truthiness check.
        Setting::set('withdrawal_always_open', $this->withdrawalAlwaysOpen ? '1' : '0');
        // 'ai' isn't built yet - this always saves 'human' regardless of what
        // the (disabled) select shows, so flipping the switch later is just
        // enabling that option in the view.
        Setting::set('verification_mode', 'human');

        Setting::set('activation_fee_enabled', $this->activationFeeEnabled ? '1' : '0');
        Setting::set('activation_fee_amount', $this->activationFeeAmount !== '' ? $this->activationFeeAmount : '0');
        Setting::set('activation_fee_grandfather_existing', $this->activationFeeGrandfatherExisting ? '1' : '0');
        Setting::set('referral_reward_amount', $this->referralRewardAmount !== '' ? $this->referralRewardAmount : '0');

        // The very first time the fee is switched on, remember the moment -
        // this is the cutoff ActivationFeeService uses to decide who counts
        // as an "existing" participant when grandfathering is on. It's only
        // ever set once; toggling the fee off and back on later never moves
        // it, so grandfathered participants stay grandfathered.
        if ($this->activationFeeEnabled && ! Setting::get('activation_fee_required_from')) {
            Setting::set('activation_fee_required_from', now()->toDateTimeString());
        }

        $this->dispatch('toast', type: 'success', message: 'Settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.settings.index')
            ->layout('components.layouts.admin', ['title' => 'Settings']);
    }
}
