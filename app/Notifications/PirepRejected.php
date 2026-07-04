<?php

namespace App\Notifications;

use App\Models\Flight;
use App\Notifications\Concerns\QueuesMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the pilot who filed a PIREP once a reviewer rejects it.
 *
 * The set of channels lives in `via()` — add `'database'`, `'mail'` (and later
 * a webhook channel) there to control how the pilot is reached. Every channel
 * has a matching `to*()` renderer below.
 */
class PirepRejected extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesMailChannel;

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
        $flight = $this->flight;

        $message = "Your flight {$flight->full_flight_number} from {$flight->departure_icao} to {$flight->arrival_icao} has been rejected.";
        if ($flight->rejection_remarks) {
            $message .= " Reason: " . $flight->rejection_remarks;
        }

        return [
            'title' => 'PIREP Rejected',
            'message' => $message,
            'url' => route('viewflight', $flight->id),
        ];
    }

    /**
     * Email representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $flight = $this->flight;

        $mail = (new MailMessage)
            ->subject("PIREP Rejected — {$flight->full_flight_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your flight {$flight->full_flight_number} from {$flight->departure_icao} to {$flight->arrival_icao} has been rejected.");

        if ($flight->rejection_remarks) {
            $mail->line("Reason: {$flight->rejection_remarks}");
        }

        return $mail
            ->action('View flight', route('viewflight', $flight->id))
            ->line('You can review the details and file a corrected PIREP if needed.');
    }
}
