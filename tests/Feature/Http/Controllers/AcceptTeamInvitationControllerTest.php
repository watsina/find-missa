<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\TeamInvitation;

use function Pest\Laravel\get;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('invitation can be accepted and user is created and added to the team.', function () {
    $data = [
        'name' => 'New User',
        'email' => 'new@user.email',
    ];
    $invitation = TeamInvitation::factory()->create($data);
    $team = $invitation->team;

    get($invitation->acceptUrl())
        ->assertRedirect();

    // user was created
    assertDatabaseHas('users', $data);
    // team member was added
    expect($team->members()->count())->toEqual(1);
    // invite was deleted
    assertDatabaseMissing('team_invitations', $data);
});
