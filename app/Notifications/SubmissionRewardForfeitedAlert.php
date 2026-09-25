<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent alongside RewardForfeited to everyone holding the verify-submissions
 * permission (same targeting as HumanReviewVerifier) so whoever approves
 * tasks finds out automatically when reward monitoring catches a deleted
 * or hidden post, instead of having to go looking for it.
 */
class SubmissionRewardForfeitedAlert extends Notification implements ShouldQueue
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
            ->subject('A held reward was forfeited')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->submission->participant->name . '\'s reward for "' . $this->submission->campaign->title . '" was not released - our monitoring check found their post removed or made private.')
            ->line('Reason: ' . $this->submission->monitor_forfeit_reason)
            ->action('View submission', route('admin.submissions.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->submission->loadMissing(['participant', 'campaign']);

        return [
            'type' => 'submission_reward_forfeited_alert',
            'submission_id' => $this->submission->id,
            'message' => $this->submission->participant->name . '\'s reward for "' . $this->submission->campaign->title . '" was forfeited: ' . $this->submission->monitor_forfeit_reason,
        ];
    }
}
