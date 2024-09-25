<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function () {
            Route::middleware('api')
                ->prefix('api/v1/')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->api(prepend: [
                \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
                \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
                'json',
            ])
            ->alias([
                'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
                'json' => \App\Http\Middleware\JsonMiddleware::class,
            ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // $schedule->command('inspire')->hourly();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
