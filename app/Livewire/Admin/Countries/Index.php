<?php

namespace App\Livewire\Admin\Countries;

use App\Models\Country;
use Livewire\Component;

class Index extends Component
{
    public ?int $editingId = null;
    public bool $payoutEnabled = false;
    public string $payoutProvider = 'paystack';
    public string $payoutSecretKey = ''; // write-only: left blank on edit, only overwrites the stored key if typed into
    public string $payoutCountrySlug = '';
    public bool $methodBank = true;
    public bool $methodMobileMoney = false;
    public bool $isActive = true;
    public bool $hasSavedKey = false; // for the blade's placeholder text only - never the key itself

    public function edit(int $id): void
    {
        $country = Country::find($id);

        if (! $country) {
            return;
        }

        $this->editingId = $country->id;
        $this->payoutEnabled = $country->payout_enabled;
        $this->payoutProvider = $country->payout_provider ?: 'paystack';
        // Never re-populate the decrypted secret key into the form - the
        // admin only sees whether one is already saved (see the blade).
        $this->payoutSecretKey = '';
        $this->hasSavedKey = filled($country->payout_secret_key);
        $this->payoutCountrySlug = $country->payout_country_slug ?: strtolower($country->name);
        $methods = $country->payoutMethods();
        $this->methodBank = in_array('bank', $methods, true);
        $this->methodMobileMoney = in_array('mobile_money', $methods, true);
        $this->isActive = $country->is_active;
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'country-payout-form');
    }

    public function save(): void
    {
        $this->validate([
            'payoutProvider' => ['required_if:payoutEnabled,true', 'nullable', 'string'],
            'payoutCountrySlug' => ['required_if:payoutEnabled,true', 'nullable', 'string', 'max:100'],
        ]);

        $country = Country::find($this->editingId);

        if (! $country) {
            $this->dispatch('close-modal');
            return;
        }

        $methods = array_values(array_filter([
            $this->methodBank ? 'bank' : null,
            $this->methodMobileMoney ? 'mobile_money' : null,
        ]));

        if ($this->payoutEnabled && empty($methods)) {
            $this->addError('payoutEnabled', 'Pick at least one payout method before enabling this country.');
            return;
        }

        // A country that has never had a key saved, and isn't the
        // Nigeria/.env default, can't actually be turned on yet - catch
        // that here rather than let it silently fail the first time
        // someone tries to withdraw.
        $willResolveKey = filled($this->payoutSecretKey) || filled($country->payout_secret_key)
            || ($this->payoutProvider === 'paystack' && $country->iso_code === 'NG');

        if ($this->payoutEnabled && ! $willResolveKey) {
            $this->addError('payoutSecretKey', 'Enter an API secret key before enabling withdrawals for this country.');
            return;
        }

        $country->forceFill([
            'is_active' => $this->isActive,
            'payout_enabled' => $this->payoutEnabled,
            'payout_provider' => $this->payoutProvider ?: null,
            'payout_country_slug' => $this->payoutCountrySlug ?: null,
            'payout_methods' => $methods,
        ]);

        // Only touch the stored key if the admin actually typed a new one -
        // leaving the field blank keeps whatever key (if any) is already
        // saved, so re-opening this form to flip another setting can never
        // accidentally wipe a working key.
        if (filled($this->payoutSecretKey)) {
            $country->payout_secret_key = $this->payoutSecretKey;
        }

        $country->save();

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Country updated.');
    }

    public function render()
    {
        return view('livewire.admin.countries.index', [
            'countries' => Country::orderBy('name')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Countries']);
    }
}
