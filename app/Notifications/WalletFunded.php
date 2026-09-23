<?php

namespace App\Notifications;

use App\Models\WalletFundingRequest;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletFunded extends Notification implements ShouldQueue
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
            ->subject('Wallet funded successfully')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your wallet has been credited with ₦' . number_format($this->fundingRequest->amount, 2) . ' via ' . ucfirst($this->fundingRequest->gateway) . '.')
            ->action('View wallet', route('wallet.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'wallet_funded',
            'amount' => $this->fundingRequest->amount,
            'message' => 'Your wallet was funded with ₦' . number_format($this->fundingRequest->amount, 2) . '.',
        ];
    }
}
