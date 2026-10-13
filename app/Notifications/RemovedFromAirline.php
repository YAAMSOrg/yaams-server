<?php

namespace App\Notifications;

use App\Notifications\Concerns\QueuesMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a user who was removed from an airline - by a manager, by an
 * instance admin, or because the airline was deleted.
 *
 * Carries the airline name/code as plain strings instead of the model: on
 * deletion the airline row is gone before the queued mail is rendered.
 */
class RemovedFromAirline extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesMailChannel;

    public function __construct(
        public string $airlineName,
        public string $airlineIcao,
        public bool $airlineDeleted = false,
    ) {
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
        return [
            'title' => 'Removed from '.$this->airlineName,
            'message' => $this->message(),
            'url' => route('portal'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You were removed from '.$this->airlineName)
            ->greeting("Hello {$notifiable->name},")
            ->line($this->message())
            ->line('You can join another airline with an invite code in the airline portal.')
            ->action('Open airline portal', route('portal'))
            ->line('Blue skies!');
    }

    private function message(): string
    {
        $airline = "{$this->airlineName} ({$this->airlineIcao})";

        return $this->airlineDeleted
            ? "{$airline} was deleted by the instance administrator, so your membership has ended."
            : "You are no longer a member of {$airline}.";
    }
}
