<?php

namespace App\Http\Middleware;

use App\Models\Register;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates the X-Register-Token header against registers.registration_token
 * and binds the resolved Register onto the request for controllers to use.
 *
 * Why this exists in addition to normal session auth: a cashier's session
 * cookie proves WHO they are, but not WHICH physical device/register they're
 * on -- and for offline sync, the register (and therefore its warehouse) is
 * what determines which warehouse's stock a synced sale should affect. A
 * stolen session cookie alone is also not enough to sync sales against a
 * register it was never paired with, since the token is a separate
 * long-lived secret issued at register setup time (an admin action) and
 * stored in that device's browser storage, not derived from the user login.
 */
class CheckRegisterSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Register-Token');

        if (!$token) {
            return response()->json(['message' => 'Missing register token.'], 401);
        }

        $register = Register::query()
            ->where('registration_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$register) {
            return response()->json(['message' => 'Invalid or inactive register.'], 401);
        }

        $register->touchLastSeen();
        $request->attributes->set('register', $register);

        return $next($request);
    }
}
