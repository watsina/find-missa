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
    $user->roles()->attach(Role::factory(5)->create(), ['team_id' => $team->id]);

    // assign to another team with other roles
    $anotherTeam = Team::factory()->create();
    $anotherTeam->members()->attach($user);
    $user->roles()->attach(Role::factory(3)->create(), ['team_id' => $anotherTeam->id]);

    get('/api/v1/team/'.$team->id.'/members/'.$member->id)
        ->assertOk()
        ->assertJsonCount(5, 'data.roles');
});

test('can update a members roles.', function () {
    $this->actingAsSuperAdminWithTeam();
    $team = Team::find(session('current_team_id'));

    $team->members()->attach($user = User::factory()->create());
    $member = TeamMember::firstWhere('user_id', $user->id);
    $user->roles()->attach(Role::factory(5)->create(), ['team_id' => $team->id]);

    $roleIds = Role::factory(2)->create()->pluck('id')->toArray();

    put('/api/v1/team/'.$team->id.'/members/'.$member->id, [
        'role_ids' => $roleIds,
    ])
        ->assertOk()
        ->assertJsonCount(2, 'data.roles');
});

test('can remove a team member with only their assigned roles for that team.', function () {
    $this->actingAsSuperAdminWithTeam();
    $team = Team::find(session('current_team_id'));

    $team->members()->attach($user = User::factory()->create());
    $member = TeamMember::firstWhere('user_id', $user->id);
    $user->roles()->attach(Role::factory(5)->create(), ['team_id' => $team->id]);

    // assign to another team with other roles
    $anotherTeam = Team::factory()->create();
    $anotherTeam->members()->attach($user);
    $user->roles()->attach(Role::factory(3)->create(), ['team_id' => $anotherTeam->id]);

    delete('/api/v1/team/'.$team->id.'/members/'.$member->id)
        ->assertNoContent();

    assertCount(0, $user->fresh()->roles);
    setPermissionsTeamId($anotherTeam);
    assertCount(3, $user->roles);
});
