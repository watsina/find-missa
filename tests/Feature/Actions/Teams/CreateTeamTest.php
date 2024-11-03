<?php

namespace Tests\Feature\Actions\Teams;

use Illuminate\Http\Request;
use App\Actions\Teams\CreateTeam;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

use Illuminate\Auth\Access\AuthorizationException;

test('teams can be created', function () {
    $this->actingAsSuperAdmin();

    $data = [
        'name' => 'Test Team',
        'country' => 'Uganda',
    ];
    $request = new Request;
    $request->setUserResolver(fn () => Auth::user());
    $request->merge($data);

    $action = new CreateTeam;

    $action->handle($request);

    assertDatabaseHas('teams', $data);
});

test('non admin can not create a team.', function () {
    $this->actingAsRandomUser();

    $data = [
        'name' => 'New Team',
        'country' => 'Uganda',
    ];

    $request = new Request;
    $request->setUserResolver(fn () => Auth::user());
    $request->merge($data);

    $this->expectException(AuthorizationException::class);

    $action = new CreateTeam;

    $action->handle($request);

    assertDatabaseMissing('teams', $data);
});
