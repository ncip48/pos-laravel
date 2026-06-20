<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Captures the current request's IP and user agent into the container as a
 * short-lived per-request binding, so ActivityLogService (and the
 * LogsActivity trait usages on models, via their event hooks) can attach
 * them to activity_log.ip_address / activity_log.user_agent without every
 * call site needing access to the Request object directly.
 *
 * Applied to the 'web' and 'admin' route groups only -- not to the offline
 * POS sync API routes, since a synced sale may be relayed well after the
 * original in-store request and the syncing device's IP/UA isn't
 * meaningful "where did this happen" audit data for that sale.
 */
class LogActivityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->instance('request.ip_address', $request->ip());
        app()->instance('request.user_agent', (string) $request->userAgent());

        return $next($request);
    }
}
