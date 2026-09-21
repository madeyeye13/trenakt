<?php

namespace App\Livewire\Auth;

use App\Models\Country;
use App\Models\User;
use App\Services\EmailVerificationService;
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

    public function mount(): void
    {
        $position = Location::get(request()->ip());

        if ($position && $position->countryCode) {
            $this->country_id = Country::where('iso_code', $position->countryCode)->value('id');
        }
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

    public function register(EmailVerificationService $verification): void
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

        $user->assignRole($this->intent);
        $user->forceFill(['active_mode' => $this->intent])->save();

        $verification->sendCode($user);

        Auth::login($user);

        $this->redirect('/verify-email', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register-form', [
            'countries' => Country::where('is_active', true)->get(),
        ]);
    }
}