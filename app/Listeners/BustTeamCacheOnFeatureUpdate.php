<?php

namespace App\Listeners;

use App\Models\Team;
use Laravel\Pennant\Events\FeatureUpdated;
use Laravel\Pennant\Events\FeatureUpdatedForAllScopes;

class BustTeamCacheOnFeatureUpdate
{
    /**
     * Handle FeatureUpdated events.
     * Touches the team's updated_at to invalidate User::info() cache.
     */
    public function handleFeatureUpdated(FeatureUpdated $event): void
    {
        if ($event->scope instanceof Team) {
            $event->scope->touch();
        }
    }

    /**
     * Handle FeatureUpdatedForAllScopes events.
     * Touches all teams to invalidate User::info() cache.
     */
    public function handleFeatureUpdatedForAllScopes(FeatureUpdatedForAllScopes $event): void
    {
        Team::query()->update(['updated_at' => now()]);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @return array<string, string>
     */
    public function subscribe(): array
    {
        return [
            FeatureUpdated::class => 'handleFeatureUpdated',
            FeatureUpdatedForAllScopes::class => 'handleFeatureUpdatedForAllScopes',
        ];
    }
}
