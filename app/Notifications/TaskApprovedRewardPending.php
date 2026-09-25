<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent instead of TaskApproved when the submission's category has
 * requires_monitoring on - see TaskService::approve(). The reward isn't in
 * the participant's earnings yet (nothing was credited), so unlike
 * TaskApproved this doesn't say money was added; it explains the hold and
 * when it's expected to clear.
 */
class TaskApprovedRewardPending extends Notification implements ShouldQueue
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
            ->subject('Your task was approved - reward on hold')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Good news, your submission for "' . $this->submission->campaign->title . '" was approved.')
            ->line('Your reward of ₦' . number_format($this->submission->campaign->rate_per_participant, 2) . ' is on hold while we confirm your post stays up, and will be released to your earnings on ' . $this->submission->monitoring_ends_at->format('M j, Y g:ia') . ' if everything checks out.')
            ->line('Please don\'t delete or hide the post before then, or the reward won\'t be released.')
            ->action('View your tasks', route('tasks.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_approved_reward_pending',
            'campaign_id' => $this->submission->campaign_id,
            'submission_id' => $this->submission->id,
            'message' => 'Your submission for "' . $this->submission->campaign->title . '" was approved. Reward on hold until ' . $this->submission->monitoring_ends_at->format('M j, g:ia') . '.',
        ];
    }
}
