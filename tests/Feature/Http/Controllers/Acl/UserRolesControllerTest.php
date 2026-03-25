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

test('can get roles of user in the team', function () {
    $this->actingAsSuperAdminWithTeam();

    $team = Team::find(session('current_team_id'));
    $user = User::factory()
        ->hasAttached($team)
        ->hasAttached(Role::factory(3)->forTeam($team))
        ->create();

    $this->get('/api/v1/user/'.$user->id.'/roles')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can not get roles user has in a different team.', function () {
    $this->actingAsSuperAdminWithTeam();

    $team = Team::find(session('current_team_id'));
    $user = User::factory()->hasAttached($team)->create();
    $otherTeam = Team::factory()->create();
    $otherTeam->members()->attach($user);

    $otherRoles = Role::factory(3)->forTeam($otherTeam)->create();
    $user->roles()->attach($otherRoles);

    $this->get('/api/v1/user/'.$user->id.'/roles')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('admin can assign roles to a user.', function () {
    $this->actingAsSuperAdminWithTeam();

    $team = Team::find(session('current_team_id'));
    $user = User::factory()->hasAttached($team)->create();
    $roleIds = Role::factory(3)->forTeam($team)->create()->pluck('id');

    $this->post('/api/v1/user/'.$user->id.'/roles', [
        'role_ids' => $roleIds->toArray(),
    ])
        ->assertOk()
        ->assertJsonCount(3, 'data');

    expect($user->fresh()->roles()->count())->toEqual(3);
});

test('admin can remove users roles.', function () {
    $this->actingAsSuperAdminWithTeam();

    $team = Team::find(session('current_team_id'));
    $user = User::factory()
        ->hasAttached($team)
        ->hasAttached(Role::factory(5)->forTeam($team))
        ->create();
    $roleIds = Role::factory(2)->forTeam($team)->create()->pluck('id');

    $this->post('/api/v1/user/'.$user->id.'/roles', [
        'role_ids' => $roleIds->toArray(),
    ])
        ->assertOk()
        ->assertJsonCount(2, 'data');

    expect($user->fresh()->roles()->count())->toEqual(2);
});
