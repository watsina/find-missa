<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SubParish;

class SubParishPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('sub-parishes.read');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SubParish $subParish): bool
    {
        return $user->can('sub-parishes.read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('sub-parishes.write');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SubParish $subParish): bool
    {
        return $user->can('sub-parishes.write');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SubParish $subParish): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SubParish $subParish): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SubParish $subParish): bool
    {
        return $user->is_admin;
    }
}
