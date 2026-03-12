<?php

use App\Providers\AppServiceProvider;
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
            ->statefulApi()
            ->throttleApi()
            ->prepend(\App\Http\Middleware\TeamsPermission::class)
            ->api(['auth.session', 'json'])
            ->redirectUsersTo(AppServiceProvider::HOME)
            ->alias([
                'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
                'json' => \App\Http\Middleware\JsonMiddleware::class,
                'admin' => \App\Http\Middleware\IsAdminMiddleware::class,
                'userWithTeam' => \App\Http\Middleware\UserWithTeam::class,
            ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // $schedule->command('inspire')->hourly();
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('01:30');
        $schedule->command('backup:monitor')->daily()->at('03:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontFlash(['current_password', 'password', 'password_confirmation']);
    })->create();
