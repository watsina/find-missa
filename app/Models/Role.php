<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope(new TeamScope);
    }

    /**
     * The team this role belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * The permissions assigned to this role.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    /**
     * The users assigned to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    /**
     * Sync permissions by replacing all current permission strings.
     *
     * @param  array<string>  $permissionNames
     */
    public function syncPermissions(array $permissionNames): static
    {
        $this->permissions()->delete();

        $records = collect($permissionNames)
            ->unique()
            ->map(fn (string $name) => ['permission' => $name])
            ->all();

        $this->permissions()->createMany($records);

        $this->unsetRelation('permissions');

        // Flush permission caches for all users with this role
        $this->users->each(fn (User $user) => $user->flushPermissionCache());

        return $this;
    }

    /**
     * Get the permission name strings for this role.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function getPermissionNames(): Collection
    {
        return $this->permissions->pluck('permission');
    }
}
