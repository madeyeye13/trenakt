<?php

namespace App\Livewire\Profile;

use App\Models\Country;
use App\Services\Payments\ActivationFeeService;
use App\Services\ReferralService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $date_of_birth = '';
    public ?int $country_id = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name ?? '';
        $this->phone = $user->phone ?? '';
        $this->date_of_birth = $user->date_of_birth?->format('Y-m-d') ?? '';
        $this->country_id = $user->country_id;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'min:7', 'max:20'],
            'date_of_birth' => ['required', 'date', 'before:-13 years', 'after:-100 years'],
            'country_id' => ['required', 'exists:countries,id'],
        ], [
            'date_of_birth.before' => 'You must be at least 13 years old.',
        ]);

        Auth::user()->forceFill([
            'name' => $this->name,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth,
            'country_id' => $this->country_id,
        ])->save();

        $this->dispatch('toast', type: 'success', message: 'Profile updated.');
    }

    public function render(ActivationFeeService $activationFee, ReferralService $referrals)
    {
        $user = Auth::user()->fresh(['bankAccount']);

        // Referral only ever applies to the participant side, and only
        // once there's an activation fee to reward a referral with - see
        // ReferralService. isParticipant decides whether the card shows at
        // all; referralSystemEnabled decides whether it shows the link or
        // "Coming soon".
        $isParticipant = $user->hasRole('participant');
        $referralSystemEnabled = $isParticipant && $activationFee->isEnabled();

        return view('livewire.profile.edit', [
            'countries' => Country::where('is_active', true)->orderBy('name')->pluck('name', 'id'),
            'user' => $user,
            'isParticipant' => $isParticipant,
            'referralSystemEnabled' => $referralSystemEnabled,
            'referralLink' => $referralSystemEnabled ? $referrals->linkFor($user) : null,
            'referralCode' => $referralSystemEnabled ? $user->referralCode() : null,
        ])->layout('components.layouts.app', ['title' => 'Profile']);
    }
}
