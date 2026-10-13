<?php

namespace App\Notifications;

use App\Notifications\Concerns\QueuesMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a user who was added to an airline by someone else (an instance
 * admin adding a member or founding an airline on the user's behalf).
 *
 * Carries plain strings instead of the Airline model, like RemovedFromAirline,
 * so the queued mail still renders if the airline is deleted in the meantime.
 */
class AddedToAirline extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesMailChannel;

    public function __construct(
        public string $airlineName,
        public string $airlineIcao,
        public string $role,
        public bool $asOwner = false,
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
            'title' => 'Added to '.$this->airlineName,
            'message' => $this->message(),
            'url' => route('portal'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You were added to '.$this->airlineName)
            ->greeting("Hello {$notifiable->name},")
            ->line($this->message())
            ->line('Switch to the airline from the airline portal to get started.')
            ->action('Open airline portal', route('portal'))
            ->line('Blue skies!');
    }

    private function message(): string
    {
        $airline = "{$this->airlineName} ({$this->airlineIcao})";

        return $this->asOwner
            ? "{$airline} was founded for you. You are its owner and a Manager."
            : "You were added to {$airline} as {$this->role}.";
    }
}
