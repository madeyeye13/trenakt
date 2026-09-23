<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Concerns\Broadcastable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralRewardEarned extends Notification implements ShouldQueue
{
    use Broadcastable, Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected User $referredParticipant, protected float $amount)
    {
    }

    public function via(object $notifiable): array
    {
        return $this->broadcastWhenAvailable(['mail', 'database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You earned a referral reward')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line($this->referredParticipant->name . ', who you referred, just activated their account.')
            ->line('₦' . number_format($this->amount, 2) . ' has been added to your earnings.')
            ->action('View earnings', route('earnings.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'referral_reward_earned',
            'amount' => $this->amount,
            'message' => $this->referredParticipant->name . ' activated their account. You earned ₦' . number_format($this->amount, 2) . '.',
        ];
    }
}
