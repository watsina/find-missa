<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Team;
use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\UpdateTeamName;

test('index behaves as expected', function (): void {
    $this->actingAsSuperAdmin();

    Team::factory(3)->create();

    $this->get('/api/v1/teams')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('store works as expected', function () {
    $this->actingAsSuperAdmin()
        ->post('/api/v1/teams', [
            'name' => 'Test Team',
            'country' => 'Uganda',
        ])
        ->assertCreated();
});

test('store uses the create team action...', function () {
    $team = Team::factory()->create();

    $spy = $this->spy(CreateTeam::class);
    $spy->shouldReceive('handle')->andReturn($team);

    $this->actingAsSuperAdmin()
        ->post('/api/v1/teams', [
            'name' => 'Test Team',
            'country' => 'Uganda',
        ]);

    $spy->shouldHaveReceived('handle');
});

test('show behaves as expected', function (): void {
    $this->actingAsSuperAdmin();

    $team = Team::factory()->create();

    $this->getJson('/api/v1/teams/'.$team->id)
        ->assertOk()
        ->assertJsonStructure([]);
});

test('update works as expected', function () {
    $this->actingAsSuperAdmin();
    $team = Team::factory()->create();

    $this->put('/api/v1/teams/'.$team->id, [
        'name' => 'Update Team',
    ])->assertNoContent();
});

test('udpate uses the update team action...', function () {
    $spy = $this->spy(UpdateTeamName::class);

    $team = Team::factory()->create();
    $this->actingAsSuperAdmin()
        ->put('/api/v1/teams/'.$team->id, [
            'name' => 'Update Team',
        ]);

    $spy->shouldHaveReceived('handle');
});
