<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to every staff member holding manage-users the moment a new
 * participant or business registers - not a hardcoded admin/super_admin
 * pair, so any role given that permission starts receiving these the
 * moment it's assigned, no code change needed. Mirrors how
 * HumanReviewVerifier notifies reviewers about task submissions.
 */
class UserRegistered extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected User $registered)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->broadcastWhenAvailable(['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New account registered')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->registered->name . ' (' . $this->registered->email . ') just registered as a ' . $this->registered->activeMode() . '.')
            ->action('View registered users', route('admin.users.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_registered',
            'user_id' => $this->registered->id,
            'message' => $this->registered->name . ' registered as a ' . $this->registered->activeMode() . '.',
        ];
    }
}
