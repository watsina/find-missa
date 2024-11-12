<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Team;
use App\Models\User;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;

test('user switches team successfully', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user);

    $this->actingAsUser($user)
        ->put('/api/v1/switch-team', ['team_id' => $team->id])
        ->assertOk();

    assertEquals($team->id, $user->fresh()->current_team_id);
});

test('user can not switch to team they do not belong to', function () {
    $user = User::factory()->has(Team::factory())->create();
    $team = Team::factory()->create();

    $this->actingAsUser($user)
        ->put('/api/v1/switch-team', ['team_id' => $team->id])
        ->assertNotFound();

    assertNotEquals($team->id, $user->fresh()->current_team_id);
});
