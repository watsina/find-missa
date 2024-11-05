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

    $team = Team::find(session('current_team_id'));
    $user = User::factory()->hasAttached($team)->hasAttached(Role::factory(3), ['team_id' => $team->id])->create();

    $this->get('/api/v1/user/'.$user->id.'/roles')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('admin can assign roles to a user.', function () {
    //
})->todo();

test('admin can get the users roles.', function () {
    //
})->todo();

test('admin can remove users roles.', function () {
    //
})->todo();
