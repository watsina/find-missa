<?php

namespace Tests\Feature\Actions\Teams;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Actions\Teams\CreateTeam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

test('teams can be created', function () {
    $this->actingAsSuperAdmin();

    $request = new Request;
    $request->setUserResolver(fn () => Auth::user());
    $request->merge([
        'name' => 'Test Team',
        'country' => 'Uganda',
    ]);

    $action = new CreateTeam;

    $action->handle($request);

    expect(Team::count())->toEqual(1);
    expect(Team::first()->name)->toEqual('Test Team');
});

test('non admin can not create a team.', function () {
    $this->actingAsRandomUser();

    $request = new Request;
    $request->setUserResolver(fn () => Auth::user());
    $request->merge([
        'name' => 'Test Team',
        'country' => 'Uganda',
    ]);

    $this->expectException(AuthorizationException::class);

    $action = new CreateTeam;

    $action->handle($request);

    expect(Team::count())->toEqual(0);
});
