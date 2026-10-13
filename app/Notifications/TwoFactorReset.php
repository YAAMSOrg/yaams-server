<?php

namespace App\Notifications;

use App\Models\Setting;
use App\Notifications\Concerns\QueuesMailChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to a user after an instance admin removed their two-factor
 * authentication via `yaams:reset-2fa` (account recovery).
 *
 * This is a security notice, so unlike the PIREP notifications the `mail`
 * channel ignores the user's `email_notifications` preference - the user must
 * learn about the change, especially if they did not request it.
 */
class TwoFactorReset extends Notification implements ShouldQueue
{
    use Queueable;
    use QueuesMailChannel;

    /**
     * The channels this notification is delivered on.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * In-app notification payload persisted by the built-in `database` channel.
     *
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Two-Factor Authentication Removed',
            'message' => 'An administrator removed two-factor authentication from your account. You can set it up again in your security settings.',
            'url' => route('settings.security'),
        ];
    }

    /**
     * Email representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $supportEmail = Setting::get('support_email');

        return (new MailMessage)
            ->subject('Two-factor authentication removed from your account')
            ->greeting("Hello {$notifiable->name},")
            ->line('An administrator has removed two-factor authentication from your account. You can now sign in with your password only.')
            ->line('We recommend setting up two-factor authentication again and storing your new recovery codes in a safe place.')
            ->action('Open security settings', route('settings.security'))
            ->line($supportEmail
                ? "If you did not request this, contact the administrator immediately at {$supportEmail}."
                : 'If you did not request this, contact the administrator immediately.')
            ->line('Blue skies!');
    }
}
