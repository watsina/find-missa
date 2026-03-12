<?php

use App\Http\Middleware\UserWithTeam;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Middleware\JsonMiddleware;
use App\Http\Middleware\TeamsPermission;
use App\Http\Middleware\IsAdminMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\EnsureEmailIsVerified;
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
            ->prepend(TeamsPermission::class)
            ->api(['auth.session', 'json'])
            ->redirectUsersTo(AppServiceProvider::HOME)
            ->alias([
                'verified' => EnsureEmailIsVerified::class,
                'json' => JsonMiddleware::class,
                'admin' => IsAdminMiddleware::class,
                'userWithTeam' => UserWithTeam::class,
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
