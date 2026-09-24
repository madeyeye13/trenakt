<?php

namespace App\Notifications;

use App\Models\WalletFundingRequest;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The admin-facing counterpart to WalletFunded: that one tells the business
 * their own wallet was credited, this tells staff holding
 * manage-wallet-fundings that a business funded their account - by mail and
 * in the bell. There was previously no way for admin to see this at all,
 * anywhere.
 */
class BusinessWalletFunded extends Notification implements ShouldQueue
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
        $this->fundingRequest->loadMissing('user');

        return (new MailMessage)
            ->subject('A business funded their wallet')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line(($this->fundingRequest->user->name ?? 'A business') . ' funded their wallet with ₦' . number_format($this->fundingRequest->amount, 2) . ' via ' . ucfirst($this->fundingRequest->gateway) . '.')
            ->action('View wallet fundings', route('admin.wallet-fundings.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->fundingRequest->loadMissing('user');

        return [
            'type' => 'business_wallet_funded',
            'funding_request_id' => $this->fundingRequest->id,
            'message' => ($this->fundingRequest->user->name ?? 'A business') . ' funded their wallet with ₦' . number_format($this->fundingRequest->amount, 2) . '.',
        ];
    }
}
