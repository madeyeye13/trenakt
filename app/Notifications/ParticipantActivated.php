<?php

namespace App\Notifications;

use App\Models\ActivationPayment;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The admin-facing counterpart to AccountActivated: that one tells the
 * participant their own account is now active, this tells staff holding
 * manage-activation-payments that it happened. Kept as its own class (and
 * its own 'type' in toArray()) rather than reusing AccountActivated, since
 * the recipient and wording differ and NotificationBell is shared across
 * every role - reusing the same type would mislabel one side's notification
 * or the other's.
 */
class ParticipantActivated extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected ActivationPayment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->broadcastWhenAvailable(['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->payment->loadMissing('user');

        return (new MailMessage)
            ->subject('A participant activated their account')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line(($this->payment->user->name ?? 'A participant') . ' paid the ₦' . number_format($this->payment->amount, 2) . ' activation fee and can now accept tasks.')
            ->action('View activation payments', route('admin.activation-payments.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->payment->loadMissing('user');

        return [
            'type' => 'participant_activated',
            'payment_id' => $this->payment->id,
            'message' => ($this->payment->user->name ?? 'A participant') . ' activated their account.',
        ];
    }
}
