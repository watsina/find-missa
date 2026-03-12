<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\User;
use PostHog\PostHog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use App\Listeners\BustTeamCacheOnFeatureUpdate;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    final public const HOME = '/backoffice/dashboard';

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

        Relation::enforceMorphMap(config('morph'));

        Model::preventLazyLoading();

        if (config('services.posthog.enabled') && config('services.posthog.key')) {
            PostHog::init(
                config('services.posthog.key'),
                [
                    'host' => config('services.posthog.host'),
                ]
            );
        }

        Gate::before(fn (User $user, string $ability) => $user->is_admin ? true : null);

        Gate::define('viewPulse', fn (User $user) => $user->is_admin);

        Gate::define('viewLogViewer', fn (User $user) => $user->is_admin);

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Password::defaults(function () {
            $rule = Password::min(8);

            return App::isProduction()
                ? $rule->mixedCase()->numbers()->uncompromised()
                : $rule;
        });

        $this->configurePennant();
        $this->defineMacros();
    }

    /**
     * Configure Laravel Pennant feature flags.
     */
    protected function configurePennant(): void
    {
        // Use the morph map for scope serialization
        Feature::useMorphMap();

        // Default scope is the authenticated user's current team
        Feature::resolveScopeUsing(fn () => auth()->user()?->currentTeam);

        // Return 403 JSON response when feature is inactive
        EnsureFeaturesAreActive::whenInactive(
            fn (Request $request) => response()->json([
                'message' => 'This feature is not available.',
            ], 403)
        );

        // Register event subscriber for cache busting
        Event::subscribe(BustTeamCacheOnFeatureUpdate::class);
    }

    protected function defineMacros()
    {
        Collection::macro('toLower', function () {
            /** @var Collection $this */
            return $this->map(function (string $value) {
                return Str::lower($value);
            });
        });

        Builder::macro('getMorphAttributes', function (string $name): array {
            /** @var Builder $this */
            /** @var Model $model */
            $model = $this->getModel();

            return [
                "{$name}_type" => $model->getMorphClass(),
                "{$name}_id" => $model->getKey(),
            ];
        });

        Builder::macro('getCacheKey', function (string $prefix = 'model') {
            /** @var Builder $this */
            /** @var Model $model */
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
