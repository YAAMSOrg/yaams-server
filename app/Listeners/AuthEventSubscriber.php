<?php

namespace App\Listeners;

use App\Support\ActivityLevel;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;

/**
 * Records authentication events in the activity log: successful logins and
 * logouts at `info`, failed login attempts at `warning`, plus two-factor
 * enable/disable at `info`.
 *
 * handleLogin also seeds the active airline into the session. This lives here
 * (rather than in the Fortify login pipeline) so it runs for both the normal
 * and the two-factor login paths - the latter is completed by Fortify's own
 * controller, which does not run our custom authenticateThrough pipeline.
 */
class AuthEventSubscriber
{
    public function handleLogin(Login $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('login')
            ->log('User logged in');

        // Pre-select the user's first airline so the dashboard has an active
        // airline without a detour through /portal. Null when they have none.
        session()->put('activeairline', $event->user->airlines()->first());
    }

    public function handleLogout(Logout $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('logout')
            ->log('User logged out');
    }

    public function handleFailed(Failed $event): void
    {
        activity()
            ->withProperties([
                'level' => ActivityLevel::WARNING,
                'email' => $event->credentials['email'] ?? null,
            ])
            ->event('login_failed')
            ->log('Failed login attempt');
    }

    public function handleTwoFactorConfirmed(TwoFactorAuthenticationConfirmed $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('two_factor_enabled')
            ->log('Enabled two-factor authentication');
    }

    public function handleTwoFactorDisabled(TwoFactorAuthenticationDisabled $event): void
    {
        activity()
            ->causedBy($event->user)
            ->withProperties(['level' => ActivityLevel::INFO])
            ->event('two_factor_disabled')
            ->log('Disabled two-factor authentication');
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            TwoFactorAuthenticationConfirmed::class => 'handleTwoFactorConfirmed',
            TwoFactorAuthenticationDisabled::class => 'handleTwoFactorDisabled',
        ];
    }
}
