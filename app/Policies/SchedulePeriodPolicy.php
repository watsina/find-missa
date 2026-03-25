<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SchedulePeriod;

class SchedulePeriodPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->can('schedule-periods.read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SchedulePeriod $schedulePeriod): bool
    {
        if (! $user->can('schedule-periods.read')) {
            return false;
        }

        // Universal periods can be viewed by anyone with read permission
        if ($schedulePeriod->isUniversal()) {
            return true;
        }

        // Team-specific periods can only be viewed by members of that team
        return $schedulePeriod->team_id === $user->currentTeam?->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return (bool) $user->can('schedule-periods.write');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SchedulePeriod $schedulePeriod): bool
    {
        // Universal schedule periods can only be updated by super admins
        if ($schedulePeriod->isUniversal()) {
            return (bool) $user->is_super_admin;
        }

        return (bool) $user->can('schedule-periods.write');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SchedulePeriod $schedulePeriod): bool
    {
        // Universal schedule periods can only be deleted by super admins
        if ($schedulePeriod->isUniversal()) {
            return (bool) $user->is_super_admin;
        }

        return (bool) $user->can('schedule-periods.write');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SchedulePeriod $schedulePeriod): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SchedulePeriod $schedulePeriod): bool
    {
        // Universal schedule periods can only be permanently deleted by super admins
        if ($schedulePeriod->isUniversal()) {
            return (bool) $user->is_super_admin;
        }

        return (bool) $user->is_admin;
    }
}
