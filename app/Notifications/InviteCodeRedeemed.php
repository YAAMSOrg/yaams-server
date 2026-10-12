<?php

namespace App\Notifications;

use App\Models\InviteCode;
use App\Notifications\Concerns\QueuesMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the creator of an invite code once another user redeems it to join
 * the airline.
 *
 * The set of channels lives in `via()` — `database` (in-app bell) is always on,
 * `mail` is added only when the creator has email notifications enabled. Every
 * channel has a matching `to*()` renderer below.
 */
class InviteCodeRedeemed extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesMailChannel;

    public function __construct(public InviteCode $inviteCode)
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
        $code = $this->inviteCode;

        return [
            'title' => 'Invite Code Redeemed',
            'message' => "{$code->usedBy->name} used your invite code {$code->code} and joined {$code->airline->name} as {$code->role}.",
            'url' => route('invitecodes.index'),
        ];
    }

    /**
     * Email representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $code = $this->inviteCode;

        return (new MailMessage)
            ->subject("Invite Code Redeemed — {$code->code}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$code->usedBy->name} used your invite code {$code->code} and joined {$code->airline->name} as {$code->role}.")
            ->action('Manage invite codes', route('invitecodes.index'))
            ->line('Blue skies!');
    }
}
