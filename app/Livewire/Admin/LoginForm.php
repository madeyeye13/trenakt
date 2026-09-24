<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * The admin-domain login. Was aliased on the admin.trenakt.test '/login'
 * route already (routes/web.php), but this class - and the whole
 * resources/views/auth/* wrapper views the main site's /login and /register
 * routes point at - didn't exist anywhere in the app, so there was no way
 * to sign in to the admin console at all. The main-site auth pages are a
 * separate, pre-existing gap outside this change; this only covers the
 * admin login the roles/permissions work depends on.
 */
class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($this->email) . '|' . request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'Those credentials don\'t match an admin account.',
            ]);
        }

        // Not everyone who can log in at all is admin-panel staff -
        // participants/businesses share the same users table. Same "any
        // role other than participant/business" check as EnsureUserIsAdmin.
        $user = Auth::user();
        if ($user->roles->whereNotIn('name', ['participant', 'business'])->isEmpty()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'This account doesn\'t have access to the admin console.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        request()->session()->regenerate();

        $this->redirect(route('admin.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.login-form')
            ->layout('components.layouts.admin-guest', ['title' => 'Sign in']);
    }
}
