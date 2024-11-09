<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\TeamInvitation;

use function Pest\Laravel\get;
use function PHPUnit\Framework\assertTrue;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('invitation can be accepted and user is created and added to the team and given the assigned role.', function () {
    $data = [
        'name' => 'New User',
        'email' => 'new@user.email',
    ];
    $invitation = TeamInvitation::factory()->create($data);
    $team = $invitation->team;
    $role = $invitation->role;

    get($invitation->acceptUrl())
        ->assertRedirect();

    // user was created
    assertDatabaseHas('users', $data);
    // team member was added
    expect($team->members()->count())->toEqual(1);
    // user was assigned the role
    setPermissionsTeamId($team->id);
    assertTrue(User::firstWhere('email', $data['email'])->hasRole($role));
    // invite was deleted
    assertDatabaseMissing('team_invitations', $data);
});
