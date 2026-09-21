<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public string $error = '';

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->error = 'Those credentials don\'t match our records.';
            return;
        }

        if (! Auth::user()->hasAnyRole(['admin', 'super_admin'])) {
            Auth::logout();
            $this->error = 'This account does not have admin access.';
            return;
        }

        request()->session()->regenerate();

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.login-form')
            ->layout('components.layouts.admin-guest', ['title' => 'Admin login']);
    }
}