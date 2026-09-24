<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Was aliased in bootstrap/app.php ('participant' => ...) and used on every
 * /tasks and /earnings route, but the class itself didn't exist in this
 * copy of the codebase - any request hitting those routes would have fatal
 * errored on "class not found". Added here, mirroring EnsureBusinessMode's
 * pattern exactly. Unrelated to the roles/permissions work below; found
 * while touching the middleware directory for EnsureUserIsAdmin.
 */
class EnsureParticipantMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->activeMode() !== 'participant') {
            return redirect()->route('dashboard')->with('toast', [
                'type' => 'error',
                'message' => 'Switch to Earning mode to access this page.',
            ]);
        }

        return $next($request);
    }
}
