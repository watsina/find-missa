<?php

namespace Tests\Feature\Http\Controllers\Acl;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;

test('admin must have a team selected to access endpoints.', function () {
    $user = User::factory()->create();

    $this->actingAsSuperAdmin()
        ->get('/api/v1/user/'.$user->id.'/roles')
        ->assertNotFound();
});

test('User should be in the same team.', function () {
    $this->actingAsSuperAdminWithTeam();

    $user = User::factory()->has(Role::factory(3))->create();
    Team::find(session('current_team_id'))->members()->attach($user);

    $this->get('/api/v1/user/'.$user->id.'/roles')
        ->assertOk()
        ->assertJsonCount(3, 'data');
})->todo('Is currently failing...');

test('admin can assign roles to a user.', function () {
    //
})->todo();

test('admin can get the users roles.', function () {
    //
})->todo();

test('admin can remove users roles.', function () {
    //
})->todo();
