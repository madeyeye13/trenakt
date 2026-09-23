<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
