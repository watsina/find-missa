<?php

namespace Tests\Feature\Actions\Teams;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Actions\Teams\UpdateTeamName;
use Illuminate\Auth\Access\AuthorizationException;

test('team names can be updated', function () {
    $this->actingAsSuperAdmin();

    $request = new Request;
    $request->setUserResolver(fn () => Auth::user());
    $request->merge([
        'name' => 'Update Team',
    ]);
    $team = Team::factory()->create();

    $action = new UpdateTeamName;

    $action->handle($request, $team);

    expect($team->fresh()->name)->toEqual('Update Team');
});

test('non admin can not update team name', function () {
    $this->actingAsRandomUser();

    $request = new Request;
    $request->setUserResolver(fn () => Auth::user());
    $request->merge([
        'name' => 'Update Team',
    ]);
    $team = Team::factory()->create();

    $this->expectException(AuthorizationException::class);

    $action = new UpdateTeamName;

    $action->handle($request, $team);

    expect($team->fresh()->name)->not->toEqual('Update Team');
});
