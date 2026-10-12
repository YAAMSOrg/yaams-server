<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Queued variant of Laravel's email verification mail, so a slow or failing
 * SMTP server never fails registration or a profile email change. Sent via
 * `User::sendEmailVerificationNotification()`.
 */
class VerifyEmail extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;
}
