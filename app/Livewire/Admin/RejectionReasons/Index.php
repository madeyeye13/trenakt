<?php

namespace App\Livewire\Admin\RejectionReasons;

use App\Models\RejectionReason;
use Livewire\Component;

class Index extends Component
{
    public ?int $editingId = null;
    public string $label = '';
    public bool $isActive = true;

    public function create(): void
    {
        $this->editingId = null;
        $this->label = '';
        $this->isActive = true;
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'reason-form');
    }

    public function edit(int $id): void
    {
        $reason = RejectionReason::find($id);

        if (! $reason) {
            return;
        }

        $this->editingId = $reason->id;
        $this->label = $reason->label;
        $this->isActive = $reason->is_active;
        $this->resetValidation();
        $this->dispatch('open-modal', name: 'reason-form');
    }

    public function save(): void
    {
        $this->validate([
            'label' => ['required', 'string', 'min:3', 'max:150'],
        ]);

        if ($this->editingId) {
            RejectionReason::whereKey($this->editingId)->update([
                'label' => $this->label,
                'is_active' => $this->isActive,
            ]);
        } else {
            $nextSort = (RejectionReason::max('sort_order') ?? 0) + 1;

            RejectionReason::create([
                'label' => $this->label,
                'is_active' => $this->isActive,
                'sort_order' => $nextSort,
            ]);
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Rejection reason saved.');
    }

    public function toggleActive(int $id): void
    {
        $reason = RejectionReason::find($id);

        if ($reason) {
            $reason->update(['is_active' => ! $reason->is_active]);
        }
    }

    public function delete(int $id): void
    {
        RejectionReason::whereKey($id)->delete();
        $this->dispatch('close-modal', name: 'delete-reason');
        $this->dispatch('toast', type: 'success', message: 'Rejection reason deleted.');
    }

    public function render()
    {
        return view('livewire.admin.rejection-reasons.index', [
            'reasons' => RejectionReason::orderBy('sort_order')->get(),
        ])->layout('components.layouts.admin', ['title' => 'Rejection Reasons']);
    }
}
