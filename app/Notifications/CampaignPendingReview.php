<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignPendingReview extends Notification implements ShouldQueue
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
            ->subject('New campaign awaiting review')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('"' . $this->campaign->title . '" was just submitted by ' . $this->campaign->business->name . ' and needs review.')
            ->line('Budget: ₦' . number_format($this->campaign->total_budget, 2))
            ->action('Review campaign', route('admin.dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'campaign_pending_review',
            'campaign_id' => $this->campaign->id,
            'title' => $this->campaign->title,
            'message' => '"' . $this->campaign->title . '" by ' . $this->campaign->business->name . ' is awaiting review.',
        ];
    }
}