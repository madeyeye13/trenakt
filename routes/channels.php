<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| This authorizes the private per-user channel Laravel's notification
| broadcasting uses by default ("App.Models.User.{id}", see the
| Notifiable trait's receivesBroadcastNotificationsOn()). It's unused
| until real-time broadcasting (Reverb) is installed and a frontend
| Echo client subscribes to it - see App\Notifications\Concerns\Broadcastable
| and resources/views/livewire/notification-bell.blade.php.
|
| IMPORTANT: Broadcast::channel() isn't a real method on the broadcast
| manager - it falls through __call(), which resolves the *default*
| broadcast connection before registering the channel. That means
| calling it unconditionally would try to boot the "reverb" driver
| (per BROADCAST_CONNECTION in .env) on every single request, even
| though nothing here needs it yet. The class_exists() guard below
| keeps this a genuine no-op until the Pusher-protocol client that
| Reverb's driver depends on (pulled in by `composer require
| laravel/reverb`) is actually installed.
|
*/

if (class_exists(\Pusher\Pusher::class)) {
    Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });
}
