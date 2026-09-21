<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->activeMode() !== 'business') {
            return redirect()->route('dashboard')->with('toast', [
                'type' => 'error',
                'message' => 'Switch to Promoting mode to access this page.',
            ]);
        }

        return $next($request);
    }
}