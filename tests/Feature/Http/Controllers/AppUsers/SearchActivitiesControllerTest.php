<?php

namespace Tests\Feature\Http\Controllers\AppUsers;

use App\Models\Church;
use App\Models\ActivityTime;

use function Pest\Laravel\get;

use App\Models\ActivityTimeException;

test('It organises the data by start time...', function () {
    // activities that happen at different times
    $massLater = ActivityTime::factory()->daily('mass')->create(['start_time' => '18:00', 'end_time' => '18:30']);
    $massEarlier = ActivityTime::factory()->weekly(now()->dayName, 'mass')->create(['start_time' => '07:00', 'end_time' => '07:30']);
    $massInMiddle = ActivityTime::factory()->oneTime(now(), 'mass')->create(['start_time' => '13:00', 'end_time' => '13:30']);

    get(route('search', [
        'type' => 'mass',
        'date' => now()->toDateString(),
    ]))
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.id', $massEarlier->id)
        ->assertJsonPath('data.1.id', $massInMiddle->id)
        ->assertJsonPath('data.2.id', $massLater->id);
});

test('User can search activities by start time...', function () {
    // activities that happen at different times
    $massEarlier = ActivityTime::factory()->weekly(now()->dayName, 'mass')->create(['start_time' => '07:00', 'end_time' => '07:30']);
    $massInMiddle = ActivityTime::factory()->oneTime(now(), 'mass')->create(['start_time' => '13:00', 'end_time' => '13:30']);
    $massLater = ActivityTime::factory()->daily('mass')->create(['start_time' => '18:00', 'end_time' => '18:30']);

    get(route('search', [
        'type' => 'mass',
        'start_time' => '13:00',
        'date' => now()->toDateString(),
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $massInMiddle->id)
        ->assertJsonPath('data.1.id', $massLater->id);
});

test('It doesnot return activities that have exceptions for the given date.', function () {
    // activities that happen at different times
    $mass1 = ActivityTime::factory()->oneTime('2025-03-28', 'mass')->create(['start_time' => '13:00', 'end_time' => '13:30']);
    $mass2 = ActivityTime::factory()->daily('mass')->create(['start_time' => '18:00', 'end_time' => '18:30']);
    //  massWithException
    ActivityTime::factory()
        ->has(ActivityTimeException::factory()->state(['start_date' => '2025-03-28', 'end_date' => '2025-03-28']), 'exceptions')
        ->weekly('Friday', 'mass')
        ->create(['start_time' => '07:00', 'end_time' => '07:30']);

    get(route('search', [
        'type' => 'mass',
        'start_time' => '06:00',
        'date' => '2025-03-28',
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $mass1->id)
        ->assertJsonPath('data.1.id', $mass2->id);
});

test('users can search masses by date', function () {
    // activities that happen on the given date
    $adorationToday = ActivityTime::factory()->daily('adoration')->create();
    $confessionToday = ActivityTime::factory()->daily('confession')->create();
    $dailyMass = ActivityTime::factory()->daily('mass')->create();
    $weeklyMass = ActivityTime::factory()->weekly(now()->dayName, 'mass')->create();
    $weeklyAnticipationMass = ActivityTime::factory()->weekly(now()->dayName, 'anticipation-mass')->create();
    $oneTimeMass = ActivityTime::factory()->oneTime(now(), 'mass')->create();
    // activities that happen on another date
    $massOnOtherDay = ActivityTime::factory()->weekly(now()->addDay()->dayName, 'mass')->create();
    $massOnOtherDate = ActivityTime::factory()->oneTime(now()->addDay(), 'mass')->create();

    get(route('search', [
        'type' => 'mass',
        'date' => now()->toDateString(),
    ]))
        ->assertOk()
        ->assertJsonCount(4, 'data')
      // check that all the activities that happen on the given date are returned
        ->assertJsonFragment(['id' => $dailyMass->id])
        ->assertJsonFragment(['id' => $weeklyMass->id])
        ->assertJsonFragment(['id' => $weeklyAnticipationMass->id])
        ->assertJsonFragment(['id' => $oneTimeMass->id])
      // check that the activities that don't happen on that date are not returned.
        ->assertJsonMissing(['id' => $massOnOtherDay->id])
        ->assertJsonMissing(['id' => $massOnOtherDate->id])
        ->assertJsonMissing(['id' => $adorationToday->id])
        ->assertJsonMissing(['id' => $confessionToday->id]);
});

test('users are able to search adorations for a given date', function () {
    // activities that happen on the given date
    $massToday = ActivityTime::factory()->daily('mass')->create();
    $confessionToday = ActivityTime::factory()->daily('confession')->create();
    $dailyAdoration = ActivityTime::factory()->daily('adoration')->create();
    $weeklyAdoration = ActivityTime::factory()->weekly(now()->dayName, 'adoration')->create();
    $oneTimeAdoration = ActivityTime::factory()->oneTime(now(), 'adoration')->create();
    // activities that happen on another date
    $adorationOnOtherDay = ActivityTime::factory()->weekly(now()->addDay()->dayName, 'adoration')->create();
    $adorationOnOtherDate = ActivityTime::factory()->oneTime(now()->addDay(), 'adoration')->create();

    get(
        route('search', [
            'type' => 'adoration',
            'date' => now()->toDateString(),
        ])
    )
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

test('users can search confessions on a given date.', function () {
    // activities that happen on the given date
    $massToday = ActivityTime::factory()->daily('mass')->create();
    $adorationToday = ActivityTime::factory()->daily('adoration')->create();
    $dailyConfession = ActivityTime::factory()->daily('confession')->create();
    $weeklyConfession = ActivityTime::factory()->weekly(now()->dayName, 'confession')->create();
    $oneTimeConfession = ActivityTime::factory()->oneTime(now(), 'confession')->create();
    // activities that happen on another date
    $confessionOnOtherDay = ActivityTime::factory()->weekly(now()->addDay()->dayName, 'confession')->create();
    $confessionOnOtherDate = ActivityTime::factory()->oneTime(now()->addDay(), 'confession')->create();

    get(
        route('search', [
            'type' => 'confession',
            'date' => now()->toDateString(),
        ])
    )
        ->assertOk()
        ->assertJsonCount('3', 'data')
      // check that all the activities that happen on the given date are returned
        ->assertJsonFragment(['id' => $dailyConfession->id])
        ->assertJsonFragment(['id' => $weeklyConfession->id])
        ->assertJsonFragment(['id' => $oneTimeConfession->id])
      // check that the activities that don't happen on that date are not returned.
        ->assertJsonMissing(['id' => $confessionOnOtherDay->id])
        ->assertJsonMissing(['id' => $confessionOnOtherDate->id])
        ->assertJsonMissing(['id' => $massToday->id])
        ->assertJsonMissing(['id' => $adorationToday->id]);
});

test('users can search by church name and activity type on a given date.', function () {
    $church = Church::factory()->create(['name' => 'Christ The King']);

    $massAtChurch = ActivityTime::factory()->weekly(now()->dayName, 'mass')->create(['church_id' => $church->id]);
    $adorationAtChurch = ActivityTime::factory()->weekly(now()->dayName, 'adoration')->create(['church_id' => $church->id]);
    $confessionAtChurch = ActivityTime::factory()->weekly(now()->dayName, 'confession')->create(['church_id' => $church->id]);
    // activity in another church
    $massAtOtherChurch = ActivityTime::factory()->weekly(now()->dayName, 'mass')->create();
    get(
        route('search', [
            'type' => 'mass',
            'date' => now()->toDateString(),
            'church_name' => $church->name,
        ])
    )
        ->assertOk()
        ->assertJsonCount('1', 'data')
        ->assertJsonFragment(['id' => $massAtChurch->id])
      // check that the activities that don't happen on that date are not returned.
        ->assertJsonMissing(['id' => $confessionAtChurch->id])
        ->assertJsonMissing(['id' => $adorationAtChurch->id])
        ->assertJsonMissing(['id' => $massAtOtherChurch->id]);
});

test('users can search by district.', function () {
    $church = Church::factory()->create(['district' => 'Rukungiri']);

    $massInDistrict = ActivityTime::factory()->weekly(now()->dayName, 'mass')->create(['church_id' => $church->id]);
    $adorationInDistrict = ActivityTime::factory()->weekly(now()->dayName, 'adoration')->create(['church_id' => $church->id]);
    $confessionInDistrict = ActivityTime::factory()->weekly(now()->dayName, 'confession')->create(['church_id' => $church->id]);
    // activity in another church
    $massInOtherDistrict = ActivityTime::factory()->weekly(now()->dayName, 'mass')->create();
    get(
        route('search', [
            'type' => 'mass',
            'date' => now()->toDateString(),
            'district' => $church->district,
        ])
    )
        ->assertOk()
        ->assertJsonCount('1', 'data')
        ->assertJsonFragment(['id' => $massInDistrict->id])
      // check that the activities that don't happen on that date are not returned.
        ->assertJsonMissing(['id' => $confessionInDistrict->id])
        ->assertJsonMissing(['id' => $adorationInDistrict->id])
        ->assertJsonMissing(['id' => $massInOtherDistrict->id]);
});
