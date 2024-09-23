<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
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
