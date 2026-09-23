<?php

namespace App\Livewire\Admin\Withdrawals;

use App\Models\WithdrawalRequest;
use App\Services\Payments\WithdrawalService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'pending';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function runPayout(WithdrawalService $withdrawals): void
    {
        try {
            $withdrawals->processPendingWithdrawals();
        } catch (\Throwable $e) {
            Log::error('Manual payout run failed: ' . $e->getMessage());
            $this->dispatch('close-modal');
            $this->dispatch('toast', type: 'error', message: 'Payout run failed. Check the logs.');
            return;
        }

        $this->dispatch('close-modal');
        $this->dispatch('toast', type: 'success', message: 'Payout run complete.');
    }

    public function render()
    {
        $baseQuery = WithdrawalRequest::query();

        $withdrawals = (clone $baseQuery)
            ->with('user')
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->latest('requested_at')
            ->paginate(12);

        $counts = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
        ")->first();

        return view('livewire.admin.withdrawals.index', [
            'withdrawals' => $withdrawals,
            'counts' => [
                'all' => (int) $counts->total,
                'pending' => (int) $counts->pending,
                'processing' => (int) $counts->processing,
                'paid' => (int) $counts->paid,
                'failed' => (int) $counts->failed,
            ],
        ])->layout('components.layouts.admin', ['title' => 'Withdrawals']);
    }
}
