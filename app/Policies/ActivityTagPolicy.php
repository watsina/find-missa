<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ActivityTag;

class ActivityTagPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('activity-tag.read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ActivityTag $activityTag): bool
    {
        return $user->can('activity-tag.read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('activity-tag.write');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ActivityTag $activityTag): bool
    {
        // System tags cannot be updated
        if ($activityTag->is_system) {
            return false;
        }

        return $user->can('activity-tag.write');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ActivityTag $activityTag): bool
    {
        // System tags cannot be deleted
        if ($activityTag->is_system) {
            return false;
        }

        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ActivityTag $activityTag): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ActivityTag $activityTag): bool
    {
        // System tags cannot be permanently deleted
        if ($activityTag->is_system) {
            return false;
        }

        return $user->is_admin;
    }
}
