<?php

namespace App\Channels;

use App\Models\Notification as InAppNotification;
use Illuminate\Notifications\Notification;

/**
 * Persists a notification as an in-app record (the bell/notifications list).
 *
 * This is the custom counterpart to Laravel's built-in `mail` / `database`
 * channels: it writes to our existing `notifications` table via the
 * App\Models\Notification model, so the current UI keeps working unchanged.
 *
 * A notification opts into this channel by returning `'inapp'` from its
 * `via()` method and implementing `toInApp($notifiable): array`.
 */
class InAppChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): ?InAppNotification
    {
        if (! method_exists($notification, 'toInApp') || ! $notifiable->getKey()) {
            return null;
        }

        $payload = $notification->toInApp($notifiable);

        return InAppNotification::create([
            'title' => $payload['title'],
            'message' => $payload['message'],
            'url' => $payload['url'] ?? null,
            'target_id' => $notifiable->getKey(),
            'acknowledged' => false,
        ]);
    }
}
