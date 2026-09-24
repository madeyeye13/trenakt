<?php

namespace App\Livewire\Admin\Staff;

use App\Models\User;
use App\Notifications\StaffAccountCreated;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';

    /** @var array<int, string> */
    public array $selectedRoles = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->selectedRoles = [];
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'staff-form');
    }

    public function edit(int $id): void
    {
        $staff = User::findOrFail($id);

        $this->editingId = $staff->id;
        $this->name = $staff->name;
        $this->email = $staff->email;
        $this->selectedRoles = $staff->roles->pluck('name')->all();
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'staff-form');
    }

    /**
     * Roles this account can hand out. super_admin is only offered by a
     * super admin - a staff member with manage-staff but not super_admin
     * can't promote anyone (including themselves) to it.
     */
    public function assignableRoles()
    {
        $roles = Role::whereNot('name', 'participant')->whereNot('name', 'business');

        if (! auth()->user()->hasRole('super_admin')) {
            $roles->whereNot('name', 'super_admin');
        }

        return $roles->orderBy('name')->get();
    }

    public function save(): void
    {
        $isSuperAdmin = auth()->user()->hasRole('super_admin');
        $editingSelf = $this->editingId === auth()->id();

        $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->editingId),
            ],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => [Rule::exists('roles', 'name')],
        ]);

        // Defence in depth alongside assignableRoles(): even a crafted
        // request can't hand out super_admin unless the actor already has it.
        $roles = collect($this->selectedRoles);
        if (! $isSuperAdmin) {
            $roles = $roles->reject(fn ($name) => $name === 'super_admin');
        }

        if ($this->editingId) {
            $staff = User::findOrFail($this->editingId);

            // Only super_admin may change a staff email - not even the
            // account's own owner, unless they hold super_admin themselves.
            $emailChanged = $staff->email !== $this->email;
            if ($emailChanged && ! $isSuperAdmin) {
                $this->dispatch('toast', type: 'error', message: 'Only a super admin can change a staff email address.');
                return;
            }

            $staff->update([
                'name' => $this->name,
                'email' => $isSuperAdmin ? $this->email : $staff->email,
            ]);
            $staff->syncRoles($roles->all());

            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'success', message: 'Staff member updated.');
            return;
        }

        $temporaryPassword = Str::password(16);

        $staff = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($temporaryPassword),
            'email_verified_at' => now(),
        ]);
        $staff->syncRoles($roles->all());
        $staff->notify(new StaffAccountCreated($temporaryPassword));

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Staff account created. They\'ve been emailed a temporary password.');
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('close-modal', name: 'delete-staff');
            $this->dispatch('toast', type: 'error', message: 'You can\'t remove your own account.');
            return;
        }

        $staff = User::find($id);

        if (! $staff) {
            return;
        }

        if ($staff->hasRole('super_admin') && ! auth()->user()->hasRole('super_admin')) {
            $this->dispatch('close-modal', name: 'delete-staff');
            $this->dispatch('toast', type: 'error', message: 'Only a super admin can remove another super admin.');
            return;
        }

        $staff->delete();
        $this->dispatch('close-modal', name: 'delete-staff');
        $this->dispatch('toast', type: 'success', message: 'Staff account removed.');
    }

    public function render()
    {
        $staff = User::whereHas('roles', function ($q) {
            $q->whereNot('name', 'participant')->whereNot('name', 'business');
        })
            ->with('roles')
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q2) use ($term) {
                    $q2->where('name', 'like', $term)->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.staff.index', [
            'staff' => $staff,
            'assignableRoles' => $this->assignableRoles(),
            'isSuperAdmin' => auth()->user()->hasRole('super_admin'),
        ])->layout('components.layouts.admin', ['title' => 'Staff']);
    }
}
