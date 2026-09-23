<?php

namespace App\Livewire\Admin\ActivationPayments;

use App\Models\ActivationPayment;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'all';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $baseQuery = ActivationPayment::query();

        $payments = (clone $baseQuery)
            ->with('user')
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(12);

        $counts = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'successful' THEN 1 ELSE 0 END) as successful,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
        ")->first();

        return view('livewire.admin.activation-payments.index', [
            'payments' => $payments,
            'counts' => [
                'all' => (int) $counts->total,
                'pending' => (int) $counts->pending,
                'successful' => (int) $counts->successful,
                'failed' => (int) $counts->failed,
            ],
        ])->layout('components.layouts.admin', ['title' => 'Activation payments']);
    }
}
