<?php

namespace App\Livewire\Auth;

use App\Services\EmailVerificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VerifyEmailCode extends Component
{
    public string $code = '';
    public ?string $error = null;
    public ?string $status = null;

    public function verify(EmailVerificationService $verification): void
    {
        $verified = $verification->verify(Auth::user(), $this->code);

        if (! $verified) {
            $this->error = 'That code is invalid or has expired.';
            return;
        }

        $this->redirect('/dashboard', navigate: true);
    }

    public function resend(EmailVerificationService $verification): void
    {
        $verification->sendCode(Auth::user());
        $this->status = 'A new code has been sent to your email.';
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.auth.verify-email-code');
    }
}