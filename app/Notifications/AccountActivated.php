<?php

namespace App\Notifications;

use App\Models\ActivationPayment;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountActivated extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Your account is activated')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your ₦' . number_format($this->payment->amount, 2) . ' activation payment was successful. You can now accept and submit tasks.')
            ->action('Find tasks', route('tasks.discover'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'account_activated',
            'message' => 'Your account is activated. You can now accept and submit tasks.',
        ];
    }
}
