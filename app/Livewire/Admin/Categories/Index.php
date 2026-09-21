<?php

namespace App\Livewire\Admin\Categories;

use App\Models\CampaignCategory;
use Livewire\Component;

class Index extends Component
{
    public function delete(int $id): void
    {
        $category = CampaignCategory::find($id);

        if (! $category) {
            return;
        }

        if ($category->campaigns()->exists()) {
            $this->dispatch('toast', type: 'error', message: 'This category has campaigns attached and cannot be deleted. Deactivate it instead.');
            $this->dispatch('close-modal', name: 'delete-category');
            return;
        }

        $category->delete();
        $this->dispatch('close-modal', name: 'delete-category');
        $this->dispatch('toast', type: 'success', message: 'Category deleted.');
    }

    public function render()
    {
        return view('livewire.admin.categories.index', [
            'categories' => CampaignCategory::withCount('campaigns')->latest()->get(),
        ])->layout('components.layouts.admin', ['title' => 'Campaign Categories']);
    }
}