<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function mount(): void
    {
        if (session()->has('toast')) {
            $toast = session('toast');
            $this->dispatch('toast', type: $toast['type'], message: $toast['message']);
        }
    }

    public function render()
    {
        return view('livewire.dashboard')
            ->layout('components.layouts.app', ['title' => 'Dashboard']);
    }
}
