<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\TeamMember;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;
use function PHPUnit\Framework\assertCount;

test('can list all the members of the team.', function () {
    $this->actingAsSuperAdminWithTeam();
    $team = Team::find(session('current_team_id'));

    $team->members()->attach(User::factory(2)->create());

    get('/api/v1/team/'.$team->id.'/members')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('can show the member details with only the roles they have on that team.', function () {
    $this->actingAsSuperAdminWithTeam();
    $team = Team::find(session('current_team_id'));

    $team->members()->attach($user = User::factory()->create());
    $member = TeamMember::firstWhere('user_id', $user->id);

    // Create team-scoped roles and attach to user
    $teamRoles = Role::factory(5)->forTeam($team)->create();
    $user->roles()->attach($teamRoles);

    // Assign to another team with other roles
    $anotherTeam = Team::factory()->create();
    $anotherTeam->members()->attach($user);
    $otherRoles = Role::factory(3)->forTeam($anotherTeam)->create();
    $user->roles()->attach($otherRoles);

    get('/api/v1/team/'.$team->id.'/members/'.$member->id)
        ->assertOk()
        ->assertJsonCount(5, 'data.user.roles');
});

test('can update a members roles.', function () {
    $this->actingAsSuperAdminWithTeam();
    $team = Team::find(session('current_team_id'));

    $team->members()->attach($user = User::factory()->create());
    $member = TeamMember::firstWhere('user_id', $user->id);

    $oldRoles = Role::factory(5)->forTeam($team)->create();
    $user->roles()->attach($oldRoles);

    $newRoleIds = Role::factory(2)->forTeam($team)->create()->pluck('id')->toArray();

    put('/api/v1/team/'.$team->id.'/members/'.$member->id, [
        'role_ids' => $newRoleIds,
    ])
        ->assertOk()
        ->assertJsonCount(2, 'data.user.roles');
});

test('can remove a team member with only their assigned roles for that team.', function () {
    $this->actingAsSuperAdminWithTeam();
    $team = Team::find(session('current_team_id'));

    $team->members()->attach($user = User::factory()->create());
    $member = TeamMember::firstWhere('user_id', $user->id);

    $teamRoles = Role::factory(5)->forTeam($team)->create();
    $user->roles()->attach($teamRoles);

    // Assign to another team with other roles
    $anotherTeam = Team::factory()->create();
    $anotherTeam->members()->attach($user);
    $otherRoles = Role::factory(3)->forTeam($anotherTeam)->create();
    $user->roles()->attach($otherRoles);

    delete('/api/v1/team/'.$team->id.'/members/'.$member->id)
        ->assertNoContent();

    // The user should have lost their team-scoped roles when removed
    // Roles for the other team should remain
    $remainingRoles = $user->fresh()->roles()->withoutGlobalScopes()->get();
    assertCount(3, $remainingRoles);
});
