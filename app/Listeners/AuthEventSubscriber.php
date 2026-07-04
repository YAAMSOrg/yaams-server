<?php

namespace App\Listeners;

use App\Support\ActivityLevel;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

/**
 * Records authentication events in the activity log: successful logins and
 * logouts at `info`, failed login attempts at `warning`.
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

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
        ];
    }
}
