<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\ActivityTime;
use Illuminate\Support\Facades\App;
use App\Models\ActivityTimeException;

use function Pest\Laravel\deleteJson;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;

beforeEach(function () {
    Diocese::factory(3)->create();
    Parish::factory(3)->create();
    SubParish::factory(3)->create();
    Church::factory(3)->create();
    ActivityTime::factory(3)->create();
    ActivityTimeException::factory(3)->create();
    Priest::factory(3)->create();
});

test('it only runs in local environment', function () {
    // Tests run in 'testing' environment by default, so we need to set 'local'
    App::detectEnvironment(fn () => 'local');

    $this->actingAsSuperAdmin();

    deleteJson(route('clear-data'))
        ->assertNoContent();

    hasNoData();
});

test("it doesn't run in production environment", function () {
    App::detectEnvironment(fn () => 'production');

    $this->actingAsSuperAdmin();

    deleteJson(route('clear-data'))
        ->assertForbidden();

    stillHasData();
});

test("it doesn't run in staging environment", function () {
    App::detectEnvironment(fn () => 'staging');

    $this->actingAsSuperAdmin();

    deleteJson(route('clear-data'))
        ->assertForbidden();

    stillHasData();
});

test('it cannot be run by a non super admin in local environment', function () {
    App::detectEnvironment(fn () => 'local');

    $this->actingAsRandomUser(['is_super_admin' => false]);

    deleteJson(route('clear-data'))
        ->assertForbidden();

    stillHasData();
});

function stillHasData()
{
    assertNotEquals(Diocese::count(), 0);
    assertNotEquals(Parish::count(), 0);
    assertNotEquals(SubParish::count(), 0);
    assertNotEquals(Church::count(), 0);
    assertNotEquals(ActivityTime::count(), 0);
    assertNotEquals(ActivityTimeException::count(), 0);
    assertNotEquals(Priest::count(), 0);
}

function hasNoData()
{
    assertEquals(Diocese::count(), 0);
    assertEquals(Parish::count(), 0);
    assertEquals(SubParish::count(), 0);
    assertEquals(Church::count(), 0);
    assertEquals(ActivityTime::count(), 0);
    assertEquals(ActivityTimeException::count(), 0);
    assertEquals(Priest::count(), 0);
}
