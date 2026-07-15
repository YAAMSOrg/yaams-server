<?php

namespace App\Notifications;

use App\Models\AircraftImage;
use App\Notifications\Concerns\QueuesMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an airline's Managers when a member uploads a screenshot that needs
 * review before it appears in the aircraft gallery.
 *
 * Channels live in `via()`; each has a matching `to*()` renderer below.
 */
class AircraftScreenshotSubmitted extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesMailChannel;

    public function __construct(public AircraftImage $image)
    {
    }

    /**
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
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        $aircraft = $this->image->aircraft;
        $uploader = $this->image->uploader?->name ?? 'A pilot';

        return [
            'title' => 'Screenshot to review',
            'message' => "{$uploader} uploaded a screenshot of {$aircraft->registration} that is waiting for approval.",
            'url' => route('viewaircraft', $aircraft->id),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $aircraft = $this->image->aircraft;
        $uploader = $this->image->uploader?->name ?? 'A pilot';

        return (new MailMessage)
            ->subject("Screenshot to review - {$aircraft->registration}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$uploader} uploaded a screenshot of {$aircraft->registration}.")
            ->action('Review screenshot', route('viewaircraft', $aircraft->id))
            ->line('It stays hidden from the gallery until you approve it.');
    }
}
