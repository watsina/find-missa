<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    final public const HOME = '/dashboard';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::before(fn (User $user, string $ability) => $user->is_admin ? true : null);

        Gate::define('viewPulse', fn (User $user) => $user->is_admin);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Password::defaults(function () {
            $rule = Password::min(8);

            return App::isProduction()
                ? $rule->mixedCase()->numbers()->uncompromised()
                : $rule;
        });

        Builder::macro('getMorphAttributes', function (string $name): array {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $model = $this->getModel();

            return [
                "{$name}_type" => $model->getMorphClass(),
                "{$name}_id" => $model->getKey(),
            ];
        });

        Builder::macro('getCacheKey', function (string $prefix = 'model') {
            /** @var \Illuminate\Database\Eloquent\Builder $this */
            $model = $this->getModel();

            return vsprintf('%s-%s:%s:%s:%s', [
                $prefix,
                $model->getConnectionName(),
                $model->getTable(),
                $model->getKey(),
                $model->getAttribute('updated_at'),
            ]);
        });
    }
}
