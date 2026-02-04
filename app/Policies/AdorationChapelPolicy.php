<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Church;
use App\Models\AdorationChapel;

class AdorationChapelPolicy
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
    public function view(User $user, ?AdorationChapel $adorationChapel, Church $church): bool
    {
        return $user->can('activity-time.read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Church $church): bool
    {
        return $user->can('activity-time.write');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?AdorationChapel $adorationChapel, Church $church): bool
    {
        return $user->can('activity-time.write');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ?AdorationChapel $adorationChapel, Church $church): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AdorationChapel $adorationChapel): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AdorationChapel $adorationChapel): bool
    {
        return $user->is_admin;
    }
}
