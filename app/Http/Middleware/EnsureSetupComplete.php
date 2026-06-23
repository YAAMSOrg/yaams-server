<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureSetupComplete
{
    public function handle(Request $request, Closure $next)
    {
        if (User::count() === 0 && !$request->routeIs('setup.*')) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
