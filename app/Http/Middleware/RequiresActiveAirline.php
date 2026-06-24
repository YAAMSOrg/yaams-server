<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresActiveAirline
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('activeairline')) {
            return redirect()->route('portal')
                ->with('info', 'Please join an airline first.');
        }

        return $next($request);
    }
}
