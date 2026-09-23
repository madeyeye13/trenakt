<?php

namespace App\Notifications;

use App\Models\WalletFundingRequest;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletFundingFailed extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected WalletFundingRequest $fundingRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->broadcastWhenAvailable(['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Wallet funding could not be completed')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('We could not confirm your payment of ₦' . number_format($this->fundingRequest->amount, 2) . ' via ' . ucfirst($this->fundingRequest->gateway) . '.')
            ->line('If you were charged, it will be reversed by your payment provider, or you can contact support.')
            ->action('Try again', route('wallet.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'wallet_funding_failed',
            'amount' => $this->fundingRequest->amount,
            'message' => 'A payment of ₦' . number_format($this->fundingRequest->amount, 2) . ' could not be confirmed.',
        ];
    }
}
