<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Gate on the admin domain: does this user hold ANY staff role at all?
     * "Staff role" is deliberately not a fixed list - it's any role other
     * than the two platform-side roles ('participant', 'business'), so a
     * brand new role a super admin creates on the Roles page works here
     * immediately with no code change. Which admin PAGES a staff member can
     * then reach is enforced separately, per route, by Spatie's 'permission'
     * middleware (see routes/web.php) - this only decides whether they get
     * into the admin console at all.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->roles->whereNotIn('name', ['participant', 'business'])->isEmpty()) {
            abort(403);
        }

        return $next($request);
    }
}