<?php

namespace App\Livewire\Admin\Account;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public string $name = '';
    public string $email = '';

    public string $currentPassword = '';
    public string $newPassword = '';

    // Laravel's `confirmed` rule looks for "{field}_confirmation" literally
    // (it doesn't camelCase the suffix), so this property name is what
    // makes wire:model="newPassword_confirmation" + rule 'confirmed' work
    // together - renaming it breaks that pairing silently (validation would
    // never see a mismatch as an error).
    public string $newPassword_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function saveProfile(): void
    {
        $isSuperAdmin = auth()->user()->hasRole('super_admin');

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => $isSuperAdmin
                ? ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth()->id())]
                : [],
        ]);

        $data = ['name' => $this->name];

        // Even if a request is crafted to include a changed email, a
        // non-super-admin's write never touches the column - only the
        // super_admin branch above even validates it.
        if ($isSuperAdmin) {
            $data['email'] = $this->email;
        } else {
            $this->email = auth()->user()->email;
        }

        auth()->user()->update($data);

        $this->dispatch('toast', type: 'success', message: 'Profile updated.');
    }

    public function changePassword(): void
    {
        $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'newPassword' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'newPassword' => 'new password',
        ]);

        auth()->user()->update(['password' => Hash::make($this->newPassword)]);

        $this->currentPassword = '';
        $this->newPassword = '';
        $this->newPassword_confirmation = '';

        $this->dispatch('toast', type: 'success', message: 'Password changed.');
    }

    public function render()
    {
        return view('livewire.admin.account.edit', [
            'isSuperAdmin' => auth()->user()->hasRole('super_admin'),
        ])->layout('components.layouts.admin', ['title' => 'My account']);
    }
}
