<?php

namespace Tests\Feature\Http\Controllers\Acl;

use App\Models\Role;
use App\Models\Permission;

test('it returns all roles and permissions.', function () {
    Role::factory(2)->create();
    Permission::factory(3)->create();

    $this->actingAsSuperAdminWithTeam()
        ->get('/api/v1/roles-permissions')
        ->assertOk()
        ->assertJsonCount(3, 'data.permissions')
        ->assertJsonCount(2, 'data.roles');
});
