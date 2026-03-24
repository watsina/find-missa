<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class AclSeeder extends Seeder
{
    /**
     * Old slug → new slug rename map.
     *
     * Used to rename existing permission records in-place so that
     * role→permission pivot assignments are preserved. Each entry
     * is only applied once (when the old name still exists in the DB).
     *
     * @var array<string, string>
     */
    private const RENAME_MAP = [
        'diocese.read' => 'dioceses.read',
        'diocese.write' => 'dioceses.write',
        'parish.read' => 'parishes.read',
        'parish.write' => 'parishes.write',
        'subParish.read' => 'sub-parishes.read',
        'subParish.write' => 'sub-parishes.write',
        'church.read' => 'churches.read',
        'church.write' => 'churches.write',
        'activity-time.read' => 'activity-times.read',
        'activity-time.write' => 'activity-times.write',
        'priest.read' => 'priests.read',
        'priest.write' => 'priests.write',
        'holiday.read' => 'holidays.read',
        'holiday.write' => 'holidays.write',
        'schedule-period.read' => 'schedule-periods.read',
        'schedule-period.write' => 'schedule-periods.write',
        'global-exception.read' => 'global-exceptions.read',
        'global-exception.write' => 'global-exceptions.write',
        'team-member.read' => 'team-members.read',
        'team-member.write' => 'team-members.write',
        'role.read' => 'roles.read',
        'role.write' => 'roles.write',
        'action-log.read' => 'action-logs.read',
    ];

    /**
     * Run the database seeds.
     *
     * Renames any legacy permission slugs first (preserving role assignments),
     * then ensures every permission from config exists. Finally clears the
     * Spatie permission cache so the new names take effect immediately.
     */
    public function run(): void
    {
        $this->renameExistingPermissions();

        foreach (config('acl.permissions') as $permissions) {
            collect($permissions)->each(function ($permission) {
                Permission::firstOrCreate(
                    ['name' => $permission['name']],
                    ['description' => $permission['description'] ?? null]
                );
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Flush the application cache to bust User::info() cached payloads
        // which contain permission names and are cached for 5 minutes.
        cache()->flush();

        $this->command->info('Permissions seeded successfully.');
    }

    /**
     * Rename legacy permission slugs in the database.
     *
     * Updates the `name` column directly so the permission's `id` (and
     * therefore all role_has_permissions pivot rows) stays unchanged.
     */
    private function renameExistingPermissions(): void
    {
        $renamed = 0;

        foreach (self::RENAME_MAP as $oldName => $newName) {
            $permission = Permission::where('name', $oldName)->first();

            if ($permission) {
                $permission->update(['name' => $newName]);
                $renamed++;
            }
        }

        if ($renamed > 0) {
            $this->command->info("Renamed {$renamed} permission(s) to new slug format.");
        }
    }
}
