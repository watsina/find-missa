<?php

namespace App\Models\Concerns;

use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Determine if the user has the given role, scoped to the current team.
     *
     * @param  Role|string|int  $role
     */
    public function hasRole($role): bool
    {
        if ($role instanceof Role) {
            return $this->roles->contains('id', $role->id);
        }

        if (is_numeric($role)) {
            return $this->roles->contains('id', (int) $role);
        }

        return $this->roles->contains('name', $role);
    }

    /**
     * Sync the user's roles by replacing all current role assignments.
     *
     * @param  array<int>  $roleIds
     */
    public function syncRoles(array $roleIds): static
    {
        $this->roles()->sync($roleIds);
        $this->unsetRelation('roles');

        $this->flushPermissionCache();

        return $this;
    }

    /**
     * Determine if the user has the given permission through any of their roles
     * on the current team.
     */
    public function hasPermissionTo(string $permission): bool
    {
        return $this->getTeamPermissions()->contains($permission);
    }

    /**
     * Get all permission strings the user has through their roles on the current team.
     */
    public function getAllPermissions(): Collection
    {
        $teamId = $this->currentTeamId();

        if (! $teamId) {
            return collect();
        }

        $cacheKey = "user:{$this->id}:team:{$teamId}:permissions";

        return cache()->remember($cacheKey, 300, function () {
            return $this->roles()
                ->with('permissions')
                ->get()
                ->flatMap(fn (Role $role) => $role->permissions->pluck('permission'))
                ->unique()
                ->values();
        });
    }

    /**
     * Get the permission strings for the current team (cached).
     */
    protected function getTeamPermissions(): Collection
    {
        return $this->getAllPermissions();
    }

    /**
     * Resolve the current team ID from the session or the user's attribute.
     */
    protected function currentTeamId(): ?int
    {
        return session('current_team_id') ?? $this->current_team_id;
    }

    /**
     * Flush the cached permissions for this user on their current team.
     */
    public function flushPermissionCache(): void
    {
        $teamId = $this->currentTeamId();

        if ($teamId) {
            cache()->forget("user:{$this->id}:team:{$teamId}:permissions");
        }
    }
}
