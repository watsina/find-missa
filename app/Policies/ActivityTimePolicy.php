<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ActivityTime;

class ActivityTimePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('activity-times.read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ActivityTime $activityTime): bool
    {
        return $user->can('activity-times.read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('activity-times.write');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ActivityTime $activityTime): bool
    {
        return $user->can('activity-times.write');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ActivityTime $activityTime): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ActivityTime $activityTime): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ActivityTime $activityTime): bool
    {
        return $user->is_admin;
    }
}
