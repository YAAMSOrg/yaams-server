<?php

namespace App\Notifications;

use App\Models\Flight;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to an airline's reviewers (Dispatchers/Managers) when a pilot files a
 * new PIREP.
 *
 * The set of channels lives in `via()` — add `'inapp'`, `'mail'` (and later a
 * webhook channel) there to control how each notifiable is reached. Every
 * channel has a matching `to*()` renderer below.
 */
class PirepFiled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Flight $flight)
    {
    }

    /**
     * The channels this notification is delivered on.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['inapp', 'mail'];
    }

    /**
     * In-app notification payload (App\Channels\InAppChannel).
     *
     * @return array<string, string>
     */
    public function toInApp(object $notifiable): array
    {
        $flight = $this->flight;

        return [
            'title' => 'New PIREP to review',
            'message' => "Pilot {$flight->pilot->name} filed a new flight ({$flight->full_flight_number}) "
                . "from {$flight->departure_icao} to {$flight->arrival_icao}.",
            'url' => route('viewflight', $flight->id),
        ];
    }

    /**
     * Email representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $flight = $this->flight;

        return (new MailMessage)
            ->subject("New PIREP to review — {$flight->full_flight_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Pilot {$flight->pilot->name} filed a new flight ({$flight->full_flight_number}) "
                . "from {$flight->departure_icao} to {$flight->arrival_icao}.")
            ->action('Review PIREP', route('viewflight', $flight->id))
            ->line('This flight is waiting for your review.');
    }
}
