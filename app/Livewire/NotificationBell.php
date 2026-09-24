<?php

namespace App\Livewire;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAsRead(string $id): void
    {
        $notification = Auth::user()->notifications()->whereKey($id)->first();

        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    /**
     * Where a notification should send the viewer when they click it.
     * Kept here (server side) rather than guessed in the view.
     */
    public function urlFor(DatabaseNotification $notification): ?string
    {
        $data = $notification->data;
        $campaignId = $data['campaign_id'] ?? null;

        return match ($data['type'] ?? null) {
            'campaign_pending_review' => $campaignId ? route('admin.campaigns.show', $campaignId) : route('admin.campaigns.index'),
            'campaign_submitted', 'campaign_approved', 'campaign_rejected' => $campaignId ? route('campaigns.submissions', $campaignId) : route('campaigns.index'),
            'wallet_funded', 'wallet_funding_failed' => route('wallet.index'),
            'task_submission_received' => route('admin.submissions.index'),
            'task_approved', 'task_rejected' => route('tasks.index'),
            'withdrawal_processed', 'withdrawal_failed' => route('earnings.index'),
            'referral_reward_earned' => route('earnings.index'),
            'account_activated' => route('tasks.discover'),
            'user_registered' => route('admin.users.index'),
            'participant_activated' => route('admin.activation-payments.index'),
            'business_wallet_funded' => route('admin.wallet-fundings.index'),
            default => null,
        };
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.notification-bell', [
            'notifications' => $user->notifications()->latest()->limit(8)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
