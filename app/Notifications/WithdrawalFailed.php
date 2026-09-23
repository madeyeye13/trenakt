<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalFailed extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected WithdrawalRequest $withdrawal)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->broadcastWhenAvailable(['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your withdrawal could not be completed')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('We could not send ₦' . number_format($this->withdrawal->amount, 2) . ' to your bank account.')
            ->line('The amount is still available in your balance. Please check your bank details and try again on the next payout day.')
            ->action('View earnings', route('earnings.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_failed',
            'withdrawal_id' => $this->withdrawal->id,
            'message' => 'We could not send ₦' . number_format($this->withdrawal->amount, 2) . ' to your bank account.',
        ];
    }
}
