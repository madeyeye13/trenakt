<?php

namespace App\Livewire\Auth;

use App\Models\Country;
use App\Models\User;
use App\Notifications\UserRegistered;
use App\Services\EmailVerificationService;
use App\Services\Payments\ActivationFeeService;
use App\Services\ReferralService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
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
        // Location::get() calls out to a third-party geolocation API - see
        // config/location.php for the timeout/fallback tuning that keeps
        // this fast. Wrapped defensively: if every driver fails (network
        // down, all APIs unreachable), registration must still work with
        // the country field simply left for the participant to pick.
        try {
            $position = Location::get(request()->ip());
        } catch (\Throwable $e) {
            $position = null;
        }

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
            'password' => ['required', 'confirmed', $this->passwordRule()],
        ]);

        $this->dispatch('step-2-validated');
    }

    /**
     * ->uncompromised() makes a live call to the Have I Been Pwned API on
     * every submit - that's a real security check worth keeping in
     * production, but it's also the reason clicking Continue has felt slow
     * locally (a third-party network round trip inside every validation).
     * Skipped only in local development; production keeps the full check.
     */
    protected function passwordRule(): Password
    {
        $rule = Password::min(8)->mixedCase()->numbers()->symbols();

        return app()->environment('local') ? $rule : $rule->uncompromised();
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

        $this->notifyAdminsOfNewRegistration($user);

        $verification->sendCode($user);

        Auth::login($user);

        $this->redirect('/verify-email', navigate: true);
    }

    /**
     * Lets whoever holds manage-users know a new account just signed up -
     * shows up in their notification bell and inbox immediately, the same
     * permission-based pattern HumanReviewVerifier uses for task
     * submissions rather than a hardcoded admin/super_admin pair. An empty
     * recipient list (nobody holds the permission yet, e.g. a fresh install
     * before any staff exist) is a no-op, never something that blocks
     * registration itself.
     */
    private function notifyAdminsOfNewRegistration(User $user): void
    {
        $admins = User::permission('manage-users')->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new UserRegistered($user));
        }
    }

    public function render(ActivationFeeService $activationFee)
    {
        return view('livewire.auth.register-form', [
            'countries' => Country::where('is_active', true)->get(),
            'referralSystemEnabled' => $activationFee->isEnabled(),
        ]);
    }
}
