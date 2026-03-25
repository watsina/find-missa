<?php

namespace App\Models;

use Illuminate\Support\Collection;

/**
 * Config-driven permission model.
 *
 * Permissions are defined in config/acl.php and are not stored in the database.
 * This class provides a convenient API for querying the permission list.
 */
class Permission
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly string $category,
    ) {}

    /**
     * Get all permissions from config as a collection.
     *
     * @return Collection<int, self>
     */
    public static function all(): Collection
    {
        return collect(config('acl.permissions', []))
            ->flatMap(fn (array $permissions, string $category) => collect($permissions)->map(
                fn (array $p) => new self(
                    name: $p['name'],
                    description: $p['description'] ?? '',
                    category: $category,
                )
            ))
            ->values();
    }

    /**
     * Get all permission name strings.
     *
     * @return Collection<int, string>
     */
    public static function allNames(): Collection
    {
        return static::all()->pluck('name');
    }

    /**
     * Determine if a permission name exists in the config.
     */
    public static function exists(string $name): bool
    {
        return static::allNames()->contains($name);
    }

    /**
     * Get permissions grouped by category with labels.
     *
     * @return array<int, array{key: string, label: string, permissions: array}>
     */
    public static function groupedByCategory(): array
    {
        $categoryLabels = config('acl.permission_categories', []);

        return collect(config('acl.permissions', []))
            ->map(fn (array $permissions, string $categoryKey) => [
                'key' => $categoryKey,
                'label' => $categoryLabels[$categoryKey] ?? ucfirst($categoryKey),
                'permissions' => collect($permissions)->map(fn (array $p) => [
                    'name' => $p['name'],
                    'description' => $p['description'] ?? '',
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Convert to array for JSON serialization.
     *
     * @return array{name: string, description: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
