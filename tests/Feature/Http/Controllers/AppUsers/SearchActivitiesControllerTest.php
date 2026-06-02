<?php

namespace Tests\Feature\Http\Controllers\AppUsers;

use Carbon\Carbon;
use App\Models\Church;
use App\Models\ActivityTime;

use function Pest\Laravel\get;

use App\Models\ActivityTimeException;
use App\Models\GlobalActivityTimeException;

// V1 search API has been disabled - use v2 instead
// These tests are skipped until the v1 API is removed entirely
beforeEach(function () {
    $this->markTestSkipped('V1 search API disabled - use v2 instead');
});

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

test('It only returns one-time activities when there is a global exception.', function () {
    // global exception
    GlobalActivityTimeException::factory()->create([
        'start_date' => '2025-04-17', // Holy Thursday
        'end_date' => '2025-04-28', // Easter Monday
    ]);
    // Holy Thursday mass
    $lastSupper = ActivityTime::factory()->oneTime('2025-04-17', 'mass')->create(['start_time' => '18:00', 'end_time' => '20:30']);
    // Usual daily mass
    ActivityTime::factory()->daily('mass')->create(['start_time' => '18:00', 'end_time' => '18:30']);
    // Usual weekly Thursday mass
    ActivityTime::factory()->weekly('Thursday')->create(['start_time' => '18:00', 'end_time' => '18:30']);

    // search for mass on Holy Thursday
    get(route('search', [
        'type' => 'mass',
        'start_time' => '06:00',
        'date' => '2025-04-17',
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $lastSupper->id);
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

// Effective Date Filtering Tests

test('search only returns activities that are effective on the search date', function () {
    // Mass that is always effective (no dates set)
    $alwaysEffectiveMass = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => null,
        'effective_until' => null,
        'schedule_period_id' => null,
    ]);

    // Mass that is effective during summer (includes search date)
    $summerMass = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => '2024-06-01',
        'effective_until' => '2024-08-31',
        'schedule_period_id' => null,
    ]);

    // Mass that is only effective in winter (excludes search date)
    $winterMass = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => '2024-12-01',
        'effective_until' => '2025-02-28',
        'schedule_period_id' => null,
    ]);

    get(route('search', [
        'type' => 'mass',
        'date' => '2024-07-15', // Summer date
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $alwaysEffectiveMass->id])
        ->assertJsonFragment(['id' => $summerMass->id])
        ->assertJsonMissingExact(['id' => $winterMass->id, 'church_id' => $winterMass->church_id]);
});

test('search returns activities with only effective_from set when search date is after', function () {
    // Mass effective from June onwards (no end date)
    $massFromJune = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => '2024-06-01',
        'effective_until' => null,
        'schedule_period_id' => null,
    ]);

    // Mass effective from December onwards (search date is before)
    $massFromDecember = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => '2024-12-01',
        'effective_until' => null,
        'schedule_period_id' => null,
    ]);

    get(route('search', [
        'type' => 'mass',
        'date' => '2024-07-15',
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $massFromJune->id])
        ->assertJsonMissingExact(['id' => $massFromDecember->id, 'church_id' => $massFromDecember->church_id]);
});

test('search returns activities with only effective_until set when search date is before', function () {
    // Mass effective until August
    $massUntilAugust = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => null,
        'effective_until' => '2024-08-31',
        'schedule_period_id' => null,
    ]);

    // Mass effective until May (search date is after)
    $massUntilMay = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => null,
        'effective_until' => '2024-05-31',
        'schedule_period_id' => null,
    ]);

    get(route('search', [
        'type' => 'mass',
        'date' => '2024-07-15',
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $massUntilAugust->id])
        ->assertJsonMissingExact(['id' => $massUntilMay->id, 'church_id' => $massUntilMay->church_id]);
});

test('search respects effective dates for weekly activities', function () {
    $searchDate = '2024-07-15'; // A Monday in summer
    $dayName = Carbon::parse($searchDate)->dayName;

    // Weekly mass effective during the search period
    $effectiveWeeklyMass = ActivityTime::factory()->weekly($dayName, 'mass')->create([
        'effective_from' => '2024-06-01',
        'effective_until' => '2024-08-31',
        'schedule_period_id' => null,
    ]);

    // Weekly mass on the same day but not effective (future schedule)
    $futureWeeklyMass = ActivityTime::factory()->weekly($dayName, 'mass')->create([
        'effective_from' => '2024-10-01',
        'effective_until' => '2024-12-31',
        'schedule_period_id' => null,
    ]);

    get(route('search', [
        'type' => 'mass',
        'date' => $searchDate,
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $effectiveWeeklyMass->id])
        ->assertJsonMissingExact(['id' => $futureWeeklyMass->id, 'church_id' => $futureWeeklyMass->church_id]);
});

test('search respects effective dates on exact boundary dates', function () {
    // Mass effective exactly on the search date boundaries
    $massStartingToday = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => '2024-07-15',
        'effective_until' => '2024-08-15',
        'schedule_period_id' => null,
    ]);

    $massEndingToday = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => '2024-06-15',
        'effective_until' => '2024-07-15',
        'schedule_period_id' => null,
    ]);

    // Mass that ended yesterday
    $massEndedYesterday = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => '2024-06-01',
        'effective_until' => '2024-07-14',
        'schedule_period_id' => null,
    ]);

    // Mass that starts tomorrow
    $massStartsTomorrow = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => '2024-07-16',
        'effective_until' => '2024-08-31',
        'schedule_period_id' => null,
    ]);

    get(route('search', [
        'type' => 'mass',
        'date' => '2024-07-15',
    ]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $massStartingToday->id])
        ->assertJsonFragment(['id' => $massEndingToday->id])
        ->assertJsonMissingExact(['id' => $massEndedYesterday->id, 'church_id' => $massEndedYesterday->church_id])
        ->assertJsonMissingExact(['id' => $massStartsTomorrow->id, 'church_id' => $massStartsTomorrow->church_id]);
});

test('search returns activities without effective dates (permanent schedule)', function () {
    // Permanent mass (no effective dates)
    $permanentMass = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => null,
        'effective_until' => null,
        'schedule_period_id' => null,
    ]);

    // Seasonal mass that's not currently effective
    $seasonalMass = ActivityTime::factory()->daily('mass')->create([
        'effective_from' => '2024-12-01',
        'effective_until' => '2024-12-31',
        'schedule_period_id' => null,
    ]);

    get(route('search', [
        'type' => 'mass',
        'date' => '2024-07-15',
    ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment(['id' => $permanentMass->id])
        ->assertJsonMissingExact(['id' => $seasonalMass->id, 'church_id' => $seasonalMass->church_id]);
});
