<?php

namespace App\Notifications\Concerns;

/**
 * Keeps every notification "broadcast ready" without wiring Reverb today.
 *
 * Notifications using this trait build their via() channels through
 * broadcastWhenAvailable(). It only adds the 'broadcast' channel once a
 * real broadcasting driver is actually installed (e.g. by running
 * `composer require laravel/reverb`, which pulls in the Pusher-protocol
 * client Reverb's broadcaster is built on). Until then it's a no-op, so
 * notifications keep working over mail + database exactly as they do now.
 *
 * The moment that package is installed, this starts returning true and
 * every notification using this trait begins broadcasting automatically -
 * no code changes required here or anywhere else. The private channel
 * name each notification broadcasts on comes for free from Laravel's
 * default Notifiable::receivesBroadcastNotificationsOn() implementation
 * ("App.Models.User.{id}"), which routes/channels.php authorizes.
 */
trait Broadcastable
{
    protected function broadcastWhenAvailable(array $channels): array
    {
        if (class_exists(\Pusher\Pusher::class)) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }
}
