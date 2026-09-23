<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalProcessed extends Notification implements ShouldQueue
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
            ->subject('Your withdrawal was paid')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('₦' . number_format($this->withdrawal->amount, 2) . ' has been sent to your bank account.')
            ->action('View earnings', route('earnings.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'withdrawal_processed',
            'withdrawal_id' => $this->withdrawal->id,
            'message' => '₦' . number_format($this->withdrawal->amount, 2) . ' was sent to your bank account.',
        ];
    }
}
