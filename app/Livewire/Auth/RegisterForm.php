<?php

namespace App\Livewire\Auth;

use App\Models\Country;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Services\Payments\ActivationFeeService;
use App\Services\ReferralService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Stevebauman\Location\Facades\Location;

class RegisterForm extends Component
{
    public string $intent = '';
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?int $country_id = null;
    public bool $agreed_terms = false;
    public string $referralCode = '';

    public function mount(): void
    {
        $position = Location::get(request()->ip());

        if ($position && $position->countryCode) {
            $this->country_id = Country::where('iso_code', $position->countryCode)->value('id');
        }

        // Auto-fills from a shared referral link (?ref=CODE); the field
        // itself only renders when the referral system is currently on
        // (see render()), but capturing it here costs nothing either way.
        $ref = request()->query('ref');
        $this->referralCode = $ref ? strtoupper(trim((string) $ref)) : '';
    }

    public function goToStep3(): void
    {
        $this->validate([
            'intent' => ['required', 'in:participant,business'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
        ]);

        $this->dispatch('step-2-validated');
    }

    public function register(EmailVerificationService $verification, ActivationFeeService $activationFee, ReferralService $referrals): void
    {
        $this->validate([
            'intent' => ['required', 'in:participant,business'],
            'country_id' => ['required', 'exists:countries,id'],
            'agreed_terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'country_id' => $this->country_id,
        ]);

        // A referral only ever attaches to a participant, and only while
        // the activation fee (and therefore the referral system) is
        // actually switched on - a code that arrived via ?ref= or was typed
        // in is silently ignored otherwise, matching the field being
        // hidden in that state. Guarding this server-side too, not just by
        // hiding the field, since the property can still be set directly.
        if ($this->intent === 'participant' && $this->referralCode !== '' && $activationFee->isEnabled()) {
            $referrer = $referrals->resolve($this->referralCode);

            if ($referrer && $referrer->id !== $user->id) {
                $user->forceFill(['referred_by_user_id' => $referrer->id])->save();
            }
        }

        $user->assignRole($this->intent);
        $user->forceFill(['active_mode' => $this->intent])->save();

        $verification->sendCode($user);

        Auth::login($user);

        $this->redirect('/verify-email', navigate: true);
    }

    public function render(ActivationFeeService $activationFee)
    {
        return view('livewire.auth.register-form', [
            'countries' => Country::where('is_active', true)->get(),
            'referralSystemEnabled' => $activationFee->isEnabled(),
        ]);
    }
}
