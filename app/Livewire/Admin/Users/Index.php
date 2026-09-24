<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $mode = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMode(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $user = User::find($id);

        if (! $user) {
            return;
        }

        // This page only ever lists participants/businesses (see render()),
        // but re-check here too rather than trust that a staff account
        // could never reach this method with a crafted id.
        if ($user->roles->whereNotIn('name', ['participant', 'business'])->isNotEmpty()) {
            $this->dispatch('close-modal', name: 'delete-user');
            return;
        }

        $user->delete();
        $this->dispatch('close-modal', name: 'delete-user');
        $this->dispatch('toast', type: 'success', message: 'User removed.');
    }

    public function render()
    {
        $users = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['participant', 'business']);
        })
            ->with('roles')
            ->when($this->mode !== 'all', fn ($q) => $q->role($this->mode))
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($q2) use ($term) {
                    $q2->where('name', 'like', $term)->orWhere('email', 'like', $term);
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.users.index', [
            'users' => $users,
        ])->layout('components.layouts.admin', ['title' => 'Registered users']);
    }
}
