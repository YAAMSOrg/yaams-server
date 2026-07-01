<?php

namespace App\Notifications;

use App\Models\Flight;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the pilot who filed a PIREP once a reviewer accepts it.
 *
 * The set of channels lives in `via()` — add `'inapp'`, `'mail'` (and later a
 * webhook channel) there to control how the pilot is reached. Every channel
 * has a matching `to*()` renderer below.
 */
class PirepAccepted extends Notification implements ShouldQueue
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
            'title' => 'PIREP Accepted',
            'message' => "Your flight {$flight->full_flight_number} from {$flight->departure_icao} to {$flight->arrival_icao} has been accepted.",
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
            ->subject("PIREP Accepted — {$flight->full_flight_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your flight {$flight->full_flight_number} from {$flight->departure_icao} to {$flight->arrival_icao} has been accepted.")
            ->action('View flight', route('viewflight', $flight->id))
            ->line('Thank you for flying with us!');
    }
}
