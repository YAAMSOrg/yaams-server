<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MetricsAuth
{
    /**
     * Require the bearer token configured as METRICS_TOKEN (services.metrics.token).
     * When no token is configured, the metrics endpoint is disabled entirely.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.metrics.token');

        if (empty($token) || ! hash_equals($token, (string) $request->bearerToken())) {
            abort(403);
        }

        return $next($request);
    }
}
