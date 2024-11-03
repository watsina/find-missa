<?php

namespace Tests\Feature\Http\Controllers\Acl;

use App\Models\Role;
use App\Models\Ability;

test('admin can get all roles with their permissions.', function () {
    Role::factory(3)->create();

    $this->actingAsSuperAdmin()
        ->get('/api/v1/roles')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('admin can get a single role with its roles and abilities.', function () {
    $role = Role::factory()->has(Ability::factory(3))->create();

    $this->actingAsSuperAdmin()
        ->get('/api/v1/roles/'.$role->id)
        ->assertOk()
        ->assertJsonCount(3, 'data.abilities');
});

test('admin can assign abilities to a role.', function () {
    $role = Role::factory()->create();
    $abilityIds = Ability::factory(4)->create()->pluck('id');

    $this->actingAsSuperAdmin()
        ->put('/api/v1/roles/'.$role->id, [
            'ability_ids' => $abilityIds->toArray(),
        ])
        ->assertOk()
        ->assertJsonCount(4, 'data.abilities');
});

test('admin can remove abilities from a role.', function () {
    $role = Role::factory()->has(Ability::factory(5))->create();
    $abilityIds = Ability::factory(2)->create()->pluck('id');

    $this->actingAsSuperAdmin()
        ->put('/api/v1/roles/'.$role->id, [
            'ability_ids' => $abilityIds->toArray(),
        ])
        ->assertOk()
        ->assertJsonCount(2, 'data.abilities');
});
