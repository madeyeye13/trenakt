<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'business' => \App\Http\Middleware\EnsureBusinessMode::class,
            'participant' => \App\Http\Middleware\EnsureParticipantMode::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->getHost() === 'admin.trenakt.test'
                ? route('admin.login')
                : route('login');
        });

        $middleware->validateCsrfTokens(except: [
            'webhooks/paystack',
            'webhooks/flutterwave',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Spatie's `permission` middleware throws this instead of a plain
        // 403 HttpException, so it doesn't automatically resolve to
        // resources/views/errors/403.blade.php the way abort(403) does.
        // Routed here explicitly so a staff member hitting a page their
        // role isn't permitted to see gets the same branded "not
        // permitted" page either way.
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return response()->view('errors.403', [], 403);
        });
    })->create();
