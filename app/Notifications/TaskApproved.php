<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskApproved extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected CampaignSubmission $submission)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->broadcastWhenAvailable(['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your task was approved')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Good news, your submission for "' . $this->submission->campaign->title . '" was approved.')
            ->line('₦' . number_format($this->submission->campaign->rate_per_participant, 2) . ' has been added to your earnings.')
            ->action('View your tasks', route('tasks.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_approved',
            'campaign_id' => $this->submission->campaign_id,
            'submission_id' => $this->submission->id,
            'message' => 'Your submission for "' . $this->submission->campaign->title . '" was approved.',
        ];
    }
}
