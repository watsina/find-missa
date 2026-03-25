<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Replace Spatie laravel-permission tables with a simple custom ACL system.
 *
 * Spatie tables being replaced:
 *   - permissions           (permission definitions with guard_name)
 *   - model_has_permissions (direct user↔permission pivot, unused in this app)
 *   - model_has_roles       (user↔role pivot with model_type morph + team_id)
 *   - role_has_permissions  (role↔permission pivot by IDs)
 *
 * New tables:
 *   - role_permissions  (role_id + permission name string)
 *   - user_roles        (user_id + role_id, simple pivot)
 *
 * The `roles` table is kept with guard_name dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the new role_permissions table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->string('permission'); // e.g. 'dioceses.read'
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->unique(['role_id', 'permission']);
        });

        // 2. Create the new user_roles table
        Schema::create('user_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['user_id', 'role_id']);
        });

        // 3. Migrate data from Spatie tables into new tables
        $this->migrateRolePermissions();
        $this->migrateUserRoles();

        // 4. Drop Spatie pivot tables (order matters for foreign keys)
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');

        // 5. Drop the permissions table (no longer needed — permissions are config-driven)
        Schema::dropIfExists('permissions');

        // 6. Clean up the roles table: drop guard_name, fix unique index
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'name', 'guard_name']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('guard_name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['team_id', 'name']);
        });
    }

    public function down(): void
    {
        // This migration is intentionally irreversible in production.
        // The Spatie tables and data cannot be faithfully reconstructed.
        throw new \RuntimeException(
            'This migration cannot be reversed. Restore from a database backup if needed.'
        );
    }

    /**
     * Copy role↔permission associations from Spatie's ID-based pivot
     * into the new string-based role_permissions table.
     */
    private function migrateRolePermissions(): void
    {
        $rows = DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->select('role_has_permissions.role_id', 'permissions.name as permission')
            ->get();

        foreach ($rows as $row) {
            DB::table('role_permissions')->insert([
                'role_id' => $row->role_id,
                'permission' => $row->permission,
            ]);
        }
    }

    /**
     * Copy user↔role associations from Spatie's morph pivot
     * into the simple user_roles table.
     */
    private function migrateUserRoles(): void
    {
        $rows = DB::table('model_has_roles')
            ->where('model_type', 'user')
            ->select('model_id as user_id', 'role_id')
            ->get();

        foreach ($rows as $row) {
            DB::table('user_roles')->insert([
                'user_id' => $row->user_id,
                'role_id' => $row->role_id,
            ]);
        }
    }
};
