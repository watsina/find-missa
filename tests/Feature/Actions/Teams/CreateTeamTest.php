<?php

namespace Tests\Feature\Actions\Teams;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Actions\Teams\CreateTeam;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertEquals;

use Illuminate\Validation\ValidationException;

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

test('team country should be unique.', function () {
    $this->actingAsSuperAdmin();

    Team::factory()->create(['country' => 'Uganda']);

    $data = [
        'name' => 'Test Team',
        'country' => 'Uganda',
    ];
    $request = new Request;
    $request->setUserResolver(fn () => Auth::user());
    $request->merge($data);

    $action = new CreateTeam;

    expect(fn () => $action->handle($request))
        ->toThrow(ValidationException::class);

    assertEquals(1, Team::count());
});

test('validates country.', function () {
    $this->actingAsSuperAdmin();

    $data = [
        'name' => 'Test Team',
        'country' => 'no country',
    ];
    $request = new Request;
    $request->setUserResolver(fn () => Auth::user());
    $request->merge($data);

    $action = new CreateTeam;

    expect(fn () => $action->handle($request))
        ->toThrow(ValidationException::class);

    assertDatabaseMissing('teams', $data);
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
