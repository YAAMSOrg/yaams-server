<?php

namespace App\Notifications;

use App\Models\Notam;
use App\Notifications\Concerns\QueuesMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Sent to every member of an airline (except the author) when a Manager posts
 * a new NOTAM (announcement).
 *
 * The set of channels lives in `via()` — `database` (in-app bell) is always on,
 * `mail` is added only for members who have email notifications enabled. Every
 * channel has a matching `to*()` renderer below.
 */
class NotamPosted extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesMailChannel;

    public function __construct(public Notam $notam)
    {
    }

    /**
     * The channels this notification is delivered on.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email_notifications ?? true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * In-app notification payload persisted by the built-in `database` channel.
     *
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        $notam = $this->notam;

        return [
            'title' => "New NOTAM: {$notam->title}",
            'message' => Str::limit($notam->body, 140)
                . " — {$notam->airline->name}",
            'url' => route('dashboard'),
        ];
    }

    /**
     * Email representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $notam = $this->notam;

        return (new MailMessage)
            ->subject("New NOTAM — {$notam->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$notam->airline->name} posted a new announcement:")
            ->line("**{$notam->title}**")
            ->line($notam->body)
            ->action('View on your dashboard', route('dashboard'))
            ->line('Blue skies!');
    }
}
