<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Holiday;

class HolidayPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->can('holidays.read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Holiday $holiday): bool
    {
        return (bool) $user->can('holidays.read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return (bool) $user->can('holidays.write');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Holiday $holiday): bool
    {
        // Universal holidays can only be updated by super admins
        if ($holiday->is_universal && $holiday->team_id === null) {
            return (bool) $user->is_super_admin;
        }

        return (bool) $user->can('holidays.write');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Holiday $holiday): bool
    {
        // Universal holidays can only be deleted by super admins
        if ($holiday->is_universal && $holiday->team_id === null) {
            return (bool) $user->is_super_admin;
        }

        // Team-specific holidays can be deleted by users with holiday.write permission
        return (bool) $user->can('holidays.write');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Holiday $holiday): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Holiday $holiday): bool
    {
        // Universal holidays can only be permanently deleted by super admins
        if ($holiday->is_universal && $holiday->team_id === null) {
            return (bool) $user->is_super_admin;
        }

        return (bool) $user->is_admin;
    }
}
