<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Notifications\TwoFactorReset;
use App\Support\ActivityLevel;

/**
 * Account recovery: removes two-factor authentication from a user who lost
 * both their authenticator and their recovery codes, and informs them by mail.
 * Used by the `yaams:reset-2fa` console command and the admin user page.
 */
class ResetTwoFactor
{
    public function hasTwoFactor(User $user): bool
    {
        return ! is_null($user->two_factor_secret)
            || ! is_null($user->two_factor_recovery_codes)
            || ! is_null($user->two_factor_confirmed_at);
    }

    /**
     * @param  string  $via  Where the reset was triggered (`console` | `admin`).
     */
    public function __invoke(User $user, ?User $causer, string $via): void
    {
        // Cleared directly instead of via Fortify's DisableTwoFactorAuthentication:
        // its TwoFactorAuthenticationDisabled event is logged by AuthEventSubscriber
        // as caused by the user themselves, which would misrepresent who did this.
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        activity()
            ->performedOn($user)
            ->when($causer, fn ($log) => $log->causedBy($causer))
            ->withProperties(['level' => ActivityLevel::WARNING, 'via' => $via])
            ->event('two_factor_reset')
            ->log('Two-factor authentication reset by an instance admin');

        $user->notify(new TwoFactorReset);
    }
}
