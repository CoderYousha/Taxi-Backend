<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__ . '/../routes/channels.php'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'state' => false,
                'message' => 'You Must Login First'
            ], 401);
        });
    })->create();
