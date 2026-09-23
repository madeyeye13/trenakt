<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskRejected extends Notification implements ShouldQueue
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
            ->subject('Your task submission needs another look')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your submission for "' . $this->submission->campaign->title . '" was not approved.')
            ->line('Reason: ' . $this->submission->rejection_reason)
            ->line('You can try this task again whenever you\'re ready.')
            ->action('View task', route('tasks.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_rejected',
            'campaign_id' => $this->submission->campaign_id,
            'submission_id' => $this->submission->id,
            'message' => 'Your submission for "' . $this->submission->campaign->title . '" was rejected: ' . $this->submission->rejection_reason,
        ];
    }
}
