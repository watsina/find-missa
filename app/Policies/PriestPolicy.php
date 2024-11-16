<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Priest;

class PriestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('priests.read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Priest $priest): bool
    {
        return $user->can('priests.read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('priests.write');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Priest $priest): bool
    {
        return $user->can('priests.write');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Priest $priest): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Priest $priest): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Priest $priest): bool
    {
        return $user->is_admin;
    }
}
