<?php

namespace App\Http\Middleware;

use App\Models\Airline;
use App\Models\Flight;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Freezes airlines suspended by an instance admin: reads pass, every write is
 * refused. The airline is resolved from the {airline} or {flight} route
 * parameter (API) or the active airline session (web).
 */
class EnsureAirlineNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $airline = $this->resolveAirline($request);

        if ($airline?->isSuspended()) {
            if ($request->expectsJson()) {
                abort(403, $airline->suspensionMessage());
            }

            return back()->with('error', $airline->suspensionMessage());
        }

        return $next($request);
    }

    private function resolveAirline(Request $request): ?Airline
    {
        $airline = $request->route('airline');
        if ($airline instanceof Airline) {
            return $airline;
        }

        $flight = $request->route('flight');
        if ($flight instanceof Flight) {
            return $flight->airline;
        }

        $sessionAirline = $request->hasSession() ? $request->session()->get('activeairline') : null;

        return $sessionAirline ? Airline::find($sessionAirline->id) : null;
    }
}
