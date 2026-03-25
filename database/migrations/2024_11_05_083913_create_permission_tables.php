<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Originally the Spatie laravel-permission migration. Rewritten with hardcoded
 * table/column names so it no longer depends on config/permission.php (which was
 * removed when the package was replaced by the custom ACL system).
 *
 * The subsequent migration (2026_03_25_002058_replace_spatie_permission_with_custom_acl)
 * drops these tables and creates the new schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->index('team_id', 'roles_team_foreign_key_index');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['team_id', 'name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->unsignedBigInteger('team_id');
            $table->index('team_id', 'model_has_permissions_team_foreign_key_index');

            $table->primary(
                ['team_id', 'permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->unsignedBigInteger('team_id');
            $table->index('team_id', 'model_has_roles_team_foreign_key_index');

            $table->primary(
                ['team_id', 'role_id', 'model_id', 'model_type'],
                'model_has_roles_role_model_type_primary'
            );
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });
    }

    public function down(): void
    {
        Schema::drop('role_has_permissions');
        Schema::drop('model_has_roles');
        Schema::drop('model_has_permissions');
        Schema::drop('roles');
        Schema::drop('permissions');
    }
};
