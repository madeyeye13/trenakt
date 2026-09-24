<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public string $error = '';

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->error = 'Those credentials don\'t match our records.';
            return;
        }

        request()->session()->regenerate();

        // A closed browser (or anything else) between registering and
        // entering the verification code used to leave an account
        // permanently unverified but still fully usable - nothing here
        // ever checked email_verified_at before this, so logging back in
        // went straight to the dashboard regardless. Sending them to the
        // same verify-email screen they'd have hit right after registering
        // closes that gap; it already has its own "resend code" action, so
        // nothing else needs to change for them to get back in.
        if (! Auth::user()->email_verified_at) {
            $this->redirect('/verify-email', navigate: true);
            return;
        }

        $this->redirect('/dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
