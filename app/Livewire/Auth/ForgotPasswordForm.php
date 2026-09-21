<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPasswordForm extends Component
{
    public string $email = '';
    public string $status = '';

    public function send(): void
    {
        $this->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink(['email' => $this->email]);

        $this->status = $status === Password::RESET_LINK_SENT
            ? 'A password reset link has been sent to your email.'
            : 'We could not find an account with that email.';
    }

    public function render()
    {
        return view('livewire.auth.forgot-password-form');
    }
}