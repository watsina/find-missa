<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LiturgicalSchedule;

class LiturgicalSchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('activity-time.read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LiturgicalSchedule $liturgicalSchedule): bool
    {
        return $user->can('activity-time.read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('activity-time.write');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LiturgicalSchedule $liturgicalSchedule): bool
    {
        return $user->can('activity-time.write');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LiturgicalSchedule $liturgicalSchedule): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LiturgicalSchedule $liturgicalSchedule): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LiturgicalSchedule $liturgicalSchedule): bool
    {
        return $user->is_admin;
    }
}
