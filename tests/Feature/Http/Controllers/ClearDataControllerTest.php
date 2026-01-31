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

test("it doesn't run on production.", function () {
    App::detectEnvironment(fn () => 'production');

    $this->actingAsSuperAdmin();

    deleteJson(route('clear-data'))
        ->assertForbidden();

    stillHasData();
});

test('it can be run in non prod env by an admin', function () {
    $this->actingAsSuperAdmin();

    deleteJson(route('clear-data'))
        ->assertNoContent();

    hasNoData();
});

test('it cannot be run in non prod env by a non admin', function () {
    $this->actingAsRandomUser();

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
