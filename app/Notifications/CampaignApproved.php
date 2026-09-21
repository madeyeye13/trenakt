<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected Campaign $campaign)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your campaign is live')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Good news, your campaign "' . $this->campaign->title . '" has been approved and is now live.')
            ->line('Participants can start applying and submitting their work now.')
            ->action('View dashboard', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'campaign_approved',
            'campaign_id' => $this->campaign->id,
            'title' => $this->campaign->title,
            'message' => 'Your campaign "' . $this->campaign->title . '" is now live.',
        ];
    }
}