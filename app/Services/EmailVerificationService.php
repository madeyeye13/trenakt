<?php

namespace App\Services;

use App\Mail\VerificationCodeMail;
use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class EmailVerificationService
{
    public function sendCode(User $user): void
    {
        $code = (string) random_int(100000, 999999);

        $user->emailVerificationCodes()->delete();

        EmailVerificationCode::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user)->queue(new VerificationCodeMail($code));
    }

    public function verify(User $user, string $code): bool
{
    $record = $user->emailVerificationCodes()->latest()->first();

    if (! $record || $record->expires_at->isPast()) {
        return false;
    }

    if (! Hash::check($code, $record->code_hash)) {
        return false;
    }

    $user->forceFill(['email_verified_at' => now()])->save();
    $record->delete();

    Mail::to($user)->queue(new \App\Mail\WelcomeEmail($user));

    return true;
}
}