<?php

namespace App\Livewire\Admin\Roles;

use App\Support\AdminPermissions;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public ?int $editingId = null;
    public string $name = '';

    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public function create(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->selectedPermissions = [];
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'role-form');
    }

    public function edit(int $id): void
    {
        $role = Role::find($id);

        if (! $role) {
            return;
        }

        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->all();
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'role-form');
    }

    public function save(): void
    {
        // super_admin's permission set is fixed (it bypasses checks
        // entirely via Gate::before, and always gets every permission in
        // the catalog) - it can't be renamed or trimmed down here, only
        // deleted-protected below.
        if ($this->editingId && Role::find($this->editingId)?->name === 'super_admin') {
            $this->dispatch('close-modal');
            return;
        }

        $this->validate([
            'name' => [
                'required', 'string', 'min:2', 'max:60',
                Rule::unique('roles', 'name')->ignore($this->editingId),
            ],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => [Rule::in(AdminPermissions::names())],
        ]);

        if ($this->editingId) {
            $role = Role::findOrFail($this->editingId);
            $role->update(['name' => $this->name]);
        } else {
            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
        }

        $role->syncPermissions($this->selectedPermissions);

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Role saved.');
    }

    public function delete(int $id): void
    {
        $role = Role::find($id);

        if (! $role) {
            return;
        }

        if ($role->name === 'super_admin') {
            $this->dispatch('close-modal', name: 'delete-role');
            $this->dispatch('toast', type: 'error', message: 'The super admin role can\'t be deleted.');
            return;
        }

        if ($role->users()->exists()) {
            $this->dispatch('close-modal', name: 'delete-role');
            $this->dispatch('toast', type: 'error', message: 'Reassign staff off this role before deleting it.');
            return;
        }

        $role->delete();
        $this->dispatch('close-modal', name: 'delete-role');
        $this->dispatch('toast', type: 'success', message: 'Role deleted.');
    }

    public function render()
    {
        return view('livewire.admin.roles.index', [
            'roles' => Role::withCount(['permissions', 'users'])
                ->whereNot('name', 'participant')
                ->whereNot('name', 'business')
                ->orderByRaw("name = 'super_admin' desc")
                ->orderBy('name')
                ->get(),
            'groupedPermissions' => AdminPermissions::grouped(),
        ])->layout('components.layouts.admin', ['title' => 'Roles & permissions']);
    }
}
