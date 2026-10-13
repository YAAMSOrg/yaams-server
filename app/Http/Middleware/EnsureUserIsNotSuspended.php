<?php

namespace App\Http\Middleware;

use App\Support\Suspension;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Ends the web session of a user who was suspended while logged in. New logins
 * are refused by the Fortify authenticateUsing callback and API tokens by the
 * Sanctum token callback (see AppServiceProvider).
 */
class EnsureUserIsNotSuspended
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->isSuspended()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(403, Suspension::message());
            }

            return redirect()->route('login')->withErrors(['email' => Suspension::message()]);
        }

        return $next($request);
    }
}
