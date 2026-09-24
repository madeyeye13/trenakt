<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Emails a brand new staff member their temporary password. Mail-only
 * (not 'database') since there's nothing to show in the in-app bell for an
 * account that can't have logged in yet.
 */
class StaffAccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(protected string $temporaryPassword)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Trenakt admin account')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('An account has been created for you on the Trenakt admin console.')
            ->line('Email: ' . $notifiable->email)
            ->line('Temporary password: ' . $this->temporaryPassword)
            ->line('Please sign in and change your password as soon as you can.')
            ->action('Sign in', route('admin.login'));
    }
}
