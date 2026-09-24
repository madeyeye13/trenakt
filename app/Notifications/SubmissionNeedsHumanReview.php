<?php

namespace App\Notifications;

use App\Models\CampaignSubmission;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to verify-submissions permission holders only when AI-assisted
 * verification left a submission at 'submitted' instead of resolving it on
 * its own - a failed deterministic check, an AI error/timeout, a low-
 * confidence result, or a confident "reject" with no default rejection
 * reason configured to act on. Never sent for a submission AI successfully
 * auto-approved or auto-rejected, so reviewers only hear about the ones
 * that actually need them.
 *
 * In human mode, TaskSubmissionReceived (sent immediately by
 * HumanReviewVerifier) covers every submission instead - this notification
 * only exists for the AI path.
 */
class SubmissionNeedsHumanReview extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected CampaignSubmission $submission, protected string $reason)
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
            ->subject('A submission needs your review')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('AI verification checked ' . $this->submission->participant->name . '\'s submission for "' . $this->submission->campaign->title . '" but couldn\'t decide on its own.')
            ->line($this->reason)
            ->action('Review submission', route('admin.submissions.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->submission->loadMissing(['participant', 'campaign']);

        return [
            'type' => 'submission_needs_human_review',
            'submission_id' => $this->submission->id,
            'title' => $this->submission->campaign->title,
            'message' => 'AI couldn\'t decide on ' . $this->submission->participant->name . '\'s submission for "' . $this->submission->campaign->title . '" - ' . $this->reason,
        ];
    }
}
