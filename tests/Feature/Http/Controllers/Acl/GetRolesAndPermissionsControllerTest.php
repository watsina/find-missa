<?php

namespace Tests\Feature\Http\Controllers\Acl;

use App\Models\Role;
use App\Models\Permission;

test('it returns all roles and permissions.', function () {
    $user = $this->actingAsSuperAdminWithTeam();

    Role::factory(2)->create(['team_id' => session('current_team_id')]);

    $this->get('/api/v1/roles-permissions')
        ->assertOk()
        ->assertJsonCount(Permission::all()->count(), 'data.permissions')
        ->assertJsonCount(2, 'data.roles');
});
