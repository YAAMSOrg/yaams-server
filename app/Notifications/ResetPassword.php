<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Queued variant of Laravel's password reset mail, so a slow or failing SMTP
 * server never fails the "forgot password" request. Sent via
 * `User::sendPasswordResetNotification()`.
 */
class ResetPassword extends BaseResetPassword implements ShouldQueue
{
    use Queueable;
}
