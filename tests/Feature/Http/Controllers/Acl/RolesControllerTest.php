<?php

namespace Tests\Feature\Http\Controllers\Acl;

use App\Models\Role;
use App\Models\Permission;

test('admin can get all roles with their permissions.', function () {
    Role::factory(3)->create();

    $this->actingAsSuperAdmin()
        ->get('/api/v1/roles')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('admin can get a single role with its roles and permissions.', function () {
    $role = Role::factory()->has(Permission::factory(3))->create();

    $this->actingAsSuperAdmin()
        ->get('/api/v1/roles/'.$role->id)
        ->assertOk()
        ->assertJsonCount(3, 'data.permissions');
});

test('admin can assign abilities to a role.', function () {
    $role = Role::factory()->create();
    $permissionIds = Permission::factory(4)->create()->pluck('id');

    $this->actingAsSuperAdmin()
        ->put('/api/v1/roles/'.$role->id, [
            'permission_ids' => $permissionIds->toArray(),
        ])
        ->assertOk()
        ->assertJsonCount(4, 'data.permissions');
});

test('admin can remove abilities from a role.', function () {
    $role = Role::factory()->has(Permission::factory(5))->create();
    $permissionIds = Permission::factory(2)->create()->pluck('id');

    $this->actingAsSuperAdmin()
        ->put('/api/v1/roles/'.$role->id, [
            'permission_ids' => $permissionIds->toArray(),
        ])
        ->assertOk()
        ->assertJsonCount(2, 'data.permissions');
});
