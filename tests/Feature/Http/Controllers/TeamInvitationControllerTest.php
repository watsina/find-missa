<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Role;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Mail\TeamInvitationEmail;
use Illuminate\Support\Facades\Mail;

test('admin can invite a user and email is sent.', function () {
    Mail::fake();
    $this->actingAsSuperAdmin();
    $team = Team::factory()->create();
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
    $this->actingAsSuperAdmin();
    $team = Team::factory()->create();
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
    $this->actingAsSuperAdmin();

    $team = Team::factory()->has(TeamInvitation::factory()->count(3), 'invitations')->create();

    $this->get('/api/v1/team/'.$team->id.'/invitations/')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});
