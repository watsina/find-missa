<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class AclSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds permissions from config. Roles are team-scoped and should be
     * created per-team through the application UI, not via seeders.
     */
    public function run(): void
    {
        foreach (config('acl.permissions') as $permissions) {
            collect($permissions)->each(function ($permission) {
                Permission::firstOrCreate(
                    ['name' => $permission['name']],
                    ['description' => $permission['description'] ?? null]
                );
            });
        }

        $this->command->info('Permissions seeded successfully.');
    }
}
