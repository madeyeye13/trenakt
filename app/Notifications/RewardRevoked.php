<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when an admin claws back a reward on a submission that was already
 * approved and paid - see TaskService::revokeReward(). The submission's own
 * status stays 'approved' (see the migration that adds these columns), so
 * this notification carries the revocation detail itself rather than
 * pointing at TaskRejected's rejection_reason field.
 */
class RewardRevoked extends Notification implements ShouldQueue
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
            ->subject('A task reward was revoked')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('The ₦' . number_format($this->submission->campaign->rate_per_participant, 2) . ' reward for your submission on "' . $this->submission->campaign->title . '" has been revoked and removed from your earnings.')
            ->line('Reason: ' . $this->submission->reward_revocation_reason)
            ->action('View your earnings', route('earnings.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reward_revoked',
            'campaign_id' => $this->submission->campaign_id,
            'submission_id' => $this->submission->id,
            'message' => 'The reward for "' . $this->submission->campaign->title . '" was revoked: ' . $this->submission->reward_revocation_reason,
        ];
    }
}
