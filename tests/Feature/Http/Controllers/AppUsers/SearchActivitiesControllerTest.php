<?php

namespace Tests\Feature\Http\Controllers\AppUsers;

use App\Models\ActivityTime;
use Illuminate\Support\Carbon;

use function Pest\Laravel\post;
use function Pest\Laravel\travelTo;
use function Pest\Laravel\travelBack;

// set the date to Thursday Novemer 11th 2024 (date on which the findmissa.com domain was purchased)
beforeEach(fn () => travelTo(Carbon::createFromDate('2024', '11', '07')));

// reset the date
afterEach(fn () => travelBack());

test('users can search masses by date', function () {
    //activities that happen on the given date
    $adorationToday = ActivityTime::factory()->daily('adoration')->create();
    $confessionToday = ActivityTime::factory()->daily('confession')->create();
    $dailyMass = ActivityTime::factory()->daily('mass')->create();
    $weeklyMass = ActivityTime::factory()->weekly('mass', now()->dayName)->create();
    $oneTimeMass = ActivityTime::factory()->oneTime('mass', now())->create();
    // activities that happen on another date
    $massOnOtherDay = ActivityTime::factory()->weekly('mass', 'Monday')->create();
    $massOnOtherDate = ActivityTime::factory()->oneTime('mass', now()->addDay())->create();

    post(route('search'), [
        'type' => 'mass',
        'date' => now(),
    ])
        ->assertOk()
        ->assertJsonCount('3', 'data')
        // check that all the activities that happen on the given date are returned
        ->assertJsonFragment(['id' => $dailyMass->id])
        ->assertJsonFragment(['id' => $weeklyMass->id])
        ->assertJsonFragment(['id' => $oneTimeMass->id])
        // check that the activities that don't happen on that date are not returned.
        ->assertJsonMissing(['id' => $massOnOtherDay->id])
        ->assertJsonMissing(['id' => $massOnOtherDate->id])
        ->assertJsonMissing(['id' => $adorationToday->id])
        ->assertJsonMissing(['id' => $confessionToday->id]);
});

test('users are able to search adorations for a given date', function () {
    //activities that happen on the given date
    $massToday = ActivityTime::factory()->daily('mass')->create();
    $confessionToday = ActivityTime::factory()->daily('confession')->create();
    $dailyAdoration = ActivityTime::factory()->daily('adoration')->create();
    $weeklyAdoration = ActivityTime::factory()->weekly('adoration', now()->dayName)->create();
    $oneTimeAdoration = ActivityTime::factory()->oneTime('adoration', now())->create();
    // activities that happen on another date
    $adorationOnOtherDay = ActivityTime::factory()->weekly('adoration', 'Monday')->create();
    $adorationOnOtherDate = ActivityTime::factory()->oneTime('adoration', now()->addDay())->create();

    post(route('search'), [
        'type' => 'adoration',
        'date' => now(),
    ])
        ->assertOk()
        ->assertJsonCount('3', 'data')
        // check that all the activities that happen on the given date are returned
        ->assertJsonFragment(['id' => $dailyAdoration->id])
        ->assertJsonFragment(['id' => $weeklyAdoration->id])
        ->assertJsonFragment(['id' => $oneTimeAdoration->id])
        // check that the activities that don't happen on that date are not returned.
        ->assertJsonMissing(['id' => $adorationOnOtherDay->id])
        ->assertJsonMissing(['id' => $adorationOnOtherDate->id])
        ->assertJsonMissing(['id' => $massToday->id])
        ->assertJsonMissing(['id' => $confessionToday->id]);
});

test('it returns confession times for the day', function () {
    //
})->todo('users should be able to search confessions');

test('users can search by church name.', function () {
    //users should be able to search by the church name..
})->todo('users should be able to search by the church name..');

test('users can search by district.', function () {
    //users should be able to search by district that the church belongs to...
})->todo('users should be able to search by district that the church belongs to...');

test('users can search by town', function () {
    //users should be able to search by church town
})->todo('users should be able to search by church town');
