<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ModeSwitcher extends Component
{
    public function switch(): void
{
    $user = Auth::user();

    $targetMode = $user->activeMode() === 'business' ? 'participant' : 'business';

    if (! $user->hasRole($targetMode)) {
        $user->assignRole($targetMode);
    }

    $user->forceFill(['active_mode' => $targetMode])->save();

    $this->redirect('/dashboard', navigate: true);
}
    public function render()
    {
        return view('livewire.mode-switcher');
    }
}