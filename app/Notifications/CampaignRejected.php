<?php

namespace App\Notifications;

use App\Models\Campaign;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignRejected extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected Campaign $campaign, protected string $reason)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->broadcastWhenAvailable(['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your campaign was not approved')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your campaign "' . $this->campaign->title . '" was not approved.')
            ->line('Reason: ' . $this->reason)
            ->line('The full amount held for this campaign has been returned to your available wallet balance.')
            ->action('View dashboard', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'campaign_rejected',
            'campaign_id' => $this->campaign->id,
            'title' => $this->campaign->title,
            'message' => 'Your campaign "' . $this->campaign->title . '" was not approved: ' . $this->reason,
        ];
    }
}
