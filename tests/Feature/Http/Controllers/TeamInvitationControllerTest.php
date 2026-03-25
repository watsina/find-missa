<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Role;
use App\Models\TeamInvitation;
use App\Mail\TeamInvitationEmail;
use Illuminate\Support\Facades\Mail;

test('admin can invite a user and email is sent.', function () {
    Mail::fake();
    $this->actingAsSuperAdminWithTeam();
    $team = auth()->user()->currentTeam;
    $data = [
        'name' => 'hello user',
        'email' => 'invite@hello.com',
        'role_id' => Role::factory()->create()->id,
    ];

    $this->post('/api/v1/team/'.$team->id.'/invitations', $data)
        ->assertCreated();

    Mail::assertSent(TeamInvitationEmail::class);

    $this->assertDatabaseHas('team_invitations', $data);
});

test('admin can delete a user invitation.', function () {
    Mail::fake();
    $this->actingAsSuperAdminWithTeam();
    $team = auth()->user()->currentTeam;
    $data = [
        'name' => 'hello user',
        'email' => 'invite@hello.com',
        'team_id' => $team->id,
    ];
    $invitation = TeamInvitation::factory()->create($data);

    $this->delete('/api/v1/team/'.$team->id.'/invitations/'.$invitation->id)
        ->assertNoContent();

    $this->assertDatabaseMissing('team_invitations', $data);
});

test('admin can get user invitations.', function () {
    Mail::fake();
    $this->actingAsSuperAdminWithTeam();
    $team = auth()->user()->currentTeam;

    TeamInvitation::factory()->count(3)->create(['team_id' => $team->id]);

    $this->get('/api/v1/team/'.$team->id.'/invitations/')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});
