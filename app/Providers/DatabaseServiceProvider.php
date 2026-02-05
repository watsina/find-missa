<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerQueryMacros();
    }

    protected function registerQueryMacros(): void
    {
        /** @phpstan-ignore-next-line */
        Builder::macro('whereLikeInsensitive', function (string $column, string $value) {
            /** @var Builder $this */
            $driver = $this->getConnection()->getDriverName();

            if ($driver === 'pgsql') {
                return $this->where($column, 'ilike', "%{$value}%");
            }

            return $this->where($column, 'like', "%{$value}%");
        });

        /** @phpstan-ignore-next-line */
        Builder::macro('orWhereLikeInsensitive', function (string $column, string $value) {
            /** @var Builder $this */
            $driver = $this->getConnection()->getDriverName();

            if ($driver === 'pgsql') {
                return $this->orWhere($column, 'ilike', "%{$value}%");
            }

            return $this->orWhere($column, 'like', "%{$value}%");
        });
    }
}
