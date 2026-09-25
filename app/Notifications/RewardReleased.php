<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent by SubmissionMonitoringService::release() once a held reward
 * survives its monitoring window and is actually credited to the
 * participant's wallet - the moment TaskApprovedRewardPending promised.
 */
class RewardReleased extends Notification implements ShouldQueue
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
            ->subject('Your reward has been released')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your held reward for "' . $this->submission->campaign->title . '" checked out. ₦' . number_format($this->submission->campaign->rate_per_participant, 2) . ' has now been added to your earnings.')
            ->action('View your earnings', route('earnings.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reward_released',
            'campaign_id' => $this->submission->campaign_id,
            'submission_id' => $this->submission->id,
            'message' => 'Your reward for "' . $this->submission->campaign->title . '" has been released to your earnings.',
        ];
    }
}
