<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to every staff member holding the `verify-submissions` permission
 * (see TaskService::submit()) - was referenced there and in the imports but
 * this class itself didn't exist, so every task submission was throwing a
 * "class not found" error before it could even notify anyone.
 */
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
        $this->submission->loadMissing(['participant', 'campaign']);

        return (new MailMessage)
            ->subject('New task submission awaiting review')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->submission->participant->name . ' just submitted "' . $this->submission->campaign->title . '" for review.')
            ->action('Review submission', route('admin.submissions.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->submission->loadMissing(['participant', 'campaign']);

        return [
            'type' => 'task_submission_received',
            'submission_id' => $this->submission->id,
            'title' => $this->submission->campaign->title,
            'message' => $this->submission->participant->name . ' submitted "' . $this->submission->campaign->title . '" for review.',
        ];
    }
}
