<?php

namespace App\Http\Middleware;

use App\Models\Airline;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresActiveAirline
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionAirline = $request->session()->get('activeairline');

        if (!$sessionAirline) {
            return redirect()->route('portal')
                ->with('info', 'Please join an airline first.');
        }

        // The session holds a snapshot - reload it so changes made elsewhere (an
        // instance admin editing, suspending or deleting the airline, or removing
        // this member) take effect on the next request.
        $airline = Airline::find($sessionAirline->id);

        if (! $airline || ! $airline->isMember($request->user())) {
            $request->session()->forget('activeairline');

            return redirect()->route('portal')
                ->with('info', 'You no longer have access to that airline. Please choose another one.');
        }

        $request->session()->put('activeairline', $airline);

        return $next($request);
    }
}
