<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AclSeeder extends Seeder
{
    /**
     * Old slug → new slug rename map.
     *
     * Used to rename existing permission strings in role_permissions so that
     * role assignments are preserved. Each entry is only applied once
     * (when the old name still exists in the DB).
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
     * Renames any legacy permission slugs in role_permissions, then removes
     * any role_permissions entries that no longer match a valid config permission.
     * Finally flushes the application cache to bust User::info() cached payloads.
     */
    public function run(): void
    {
        $this->renameLegacyPermissions();
        $this->pruneInvalidPermissions();

        // Flush the application cache to bust User::info() cached payloads
        // which contain permission names and are cached for 5 minutes.
        cache()->flush();

        $this->command->info('ACL permissions synchronized successfully.');
    }

    /**
     * Rename legacy permission slugs in the role_permissions table.
     */
    private function renameLegacyPermissions(): void
    {
        $renamed = 0;

        foreach (self::RENAME_MAP as $oldName => $newName) {
            $count = DB::table('role_permissions')
                ->where('permission', $oldName)
                ->update(['permission' => $newName]);

            $renamed += $count;
        }

        if ($renamed > 0) {
            $this->command->info("Renamed {$renamed} permission(s) to new slug format.");
        }
    }

    /**
     * Remove role_permissions entries for permissions no longer in config.
     */
    private function pruneInvalidPermissions(): void
    {
        $validNames = Permission::allNames()->all();

        $pruned = DB::table('role_permissions')
            ->whereNotIn('permission', $validNames)
            ->delete();

        if ($pruned > 0) {
            $this->command->info("Removed {$pruned} invalid permission(s) from role_permissions.");
        }
    }
}
