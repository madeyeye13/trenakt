<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent by SubmissionMonitoringService::forfeit() when a held reward's post
 * was found removed or made private during its monitoring window. Unlike
 * RewardRevoked (an admin clawing back a reward already paid), nothing was
 * ever credited here, so there's no wallet transaction and no negative-
 * balance risk - the reward simply never gets released. The submission
 * stays 'approved', which is what blocks the participant from attempting
 * this campaign again (see TaskService::participantIsEligible()).
 */
class RewardForfeited extends Notification implements ShouldQueue
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
            ->subject('Your reward was not released')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('The reward for your submission on "' . $this->submission->campaign->title . '" was not released.')
            ->line('Reason: ' . $this->submission->monitor_forfeit_reason)
            ->line('You won\'t be able to attempt this task again.')
            ->action('View your tasks', route('tasks.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reward_forfeited',
            'campaign_id' => $this->submission->campaign_id,
            'submission_id' => $this->submission->id,
            'message' => 'Your reward for "' . $this->submission->campaign->title . '" was not released: ' . $this->submission->monitor_forfeit_reason,
        ];
    }
}
