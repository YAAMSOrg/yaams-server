<?php

namespace App\Support;

use App\Models\Setting;

/**
 * The message shown to a suspended user wherever access is refused (login form,
 * live session, API).
 */
class Suspension
{
    public static function message(): string
    {
        $supportEmail = rescue(fn () => Setting::get('support_email'), null, false);

        return $supportEmail
            ? "This account has been suspended. Please contact {$supportEmail}."
            : 'This account has been suspended.';
    }
}
