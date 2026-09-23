<?php

namespace App\Notifications;

use App\Models\Campaign;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignSubmitted extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected Campaign $campaign)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->broadcastWhenAvailable(['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your campaign is under review')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your campaign "' . $this->campaign->title . '" has been submitted and is now under review.')
            ->line('₦' . number_format($this->campaign->total_budget, 2) . ' has been held from your wallet for this campaign.')
            ->line('We will let you know as soon as it is approved and live.')
            ->action('View dashboard', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'campaign_submitted',
            'campaign_id' => $this->campaign->id,
            'title' => $this->campaign->title,
            'message' => 'Your campaign "' . $this->campaign->title . '" is under review.',
        ];
    }
}
