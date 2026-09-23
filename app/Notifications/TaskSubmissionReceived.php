<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskSubmissionReceived extends Notification implements ShouldQueue
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
            ->subject('New task submission awaiting review')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->submission->participant->name . ' submitted a task for "' . $this->submission->campaign->title . '" and it needs review.')
            ->action('Review submission', route('admin.submissions.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_submission_received',
            'campaign_id' => $this->submission->campaign_id,
            'submission_id' => $this->submission->id,
            'message' => $this->submission->participant->name . ' submitted a task for "' . $this->submission->campaign->title . '".',
        ];
    }
}
