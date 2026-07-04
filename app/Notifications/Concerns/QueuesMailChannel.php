<?php

namespace App\Notifications\Concerns;

/**
 * Keeps the in-app (`database`) notification channel synchronous while letting
 * the `mail` channel be queued.
 *
 * The notification implements `ShouldQueue`, so by default every channel would
 * be pushed onto the queue. Pinning `database` to the `sync` connection means
 * the bell/in-app notification is written immediately (instant, no worker
 * required), while `mail` falls through to the app's default queue connection
 * (`database`, or `redis` via `QUEUE_CONNECTION`). A slow or failing SMTP server
 * therefore never blocks or fails the triggering web/API request.
 */
trait QueuesMailChannel
{
    /**
     * Per-channel queue connections.
     *
     * @return array<string, string>
     */
    public function viaConnections(): array
    {
        return ['database' => 'sync'];
    }
}
