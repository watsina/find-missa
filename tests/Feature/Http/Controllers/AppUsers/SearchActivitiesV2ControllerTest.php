<?php

namespace Tests\Feature\Http\Controllers\AppUsers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Models\Holiday;
use App\Enums\DayPattern;
use App\Models\SubParish;
use App\Models\HolidayInstance;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\LiturgicalSchedule;

use function Pest\Laravel\getJson;

test('search v2 returns activities with church hierarchy for parish', function () {
    $diocese = Diocese::factory()->create(['name' => 'Kampala Archdiocese']);
    $parish = Parish::factory()->create([
        'name' => 'Christ the King Parish',
        'diocese_id' => $diocese->id,
    ]);
    $church = Church::factory()->create([
        'parishable_id' => $parish->id,
        'parishable_type' => Parish::class,
    ]);

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '10:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    $churchData = $response->json('data.0.church');
    expect($churchData)->toHaveKey('hierarchy');
    expect($churchData['hierarchy'])->toBe([
        'sub_parish' => null,
        'parish' => 'Christ the King Parish',
        'diocese' => 'Kampala Archdiocese',
    ]);
});

test('search v2 returns activities with church hierarchy for sub_parish', function () {
    $diocese = Diocese::factory()->create(['name' => 'Jinja Diocese']);
    $parish = Parish::factory()->create([
        'name' => 'St. James Parish',
        'diocese_id' => $diocese->id,
    ]);
    $subParish = SubParish::factory()->create([
        'name' => 'Holy Cross Sub-Parish',
        'parish_id' => $parish->id,
    ]);
    $church = Church::factory()->create([
        'parishable_id' => $subParish->id,
        'parishable_type' => SubParish::class,
    ]);

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '10:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    $churchData = $response->json('data.0.church');
    expect($churchData)->toHaveKey('hierarchy');
    expect($churchData['hierarchy'])->toBe([
        'sub_parish' => 'Holy Cross Sub-Parish',
        'parish' => 'St. James Parish',
        'diocese' => 'Jinja Diocese',
    ]);
});

test('search v2 does not expose internal IDs in church data', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '10:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    $churchData = $response->json('data.0.church');

    // Should NOT expose internal IDs
    expect($churchData)->not->toHaveKey('parishable_id');
    expect($churchData)->not->toHaveKey('parishable_type');
    expect($churchData)->not->toHaveKey('priest_id');
    expect($churchData)->not->toHaveKey('parish');

    // Should have hierarchy instead
    expect($churchData)->toHaveKey('hierarchy');
});

test('search v2 does not include end_time for mass activities', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '10:00',
        'end_time' => '11:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    expect($response->json('data.0.end_time'))->toBeNull();
    expect($response->json('data.0.start_time'))->toBe('10:00');
});

test('search v2 includes end_time for adoration activities', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'adoration',
        'activity_type_description' => 'adoration',
        'start_time' => '07:00',
        'end_time' => '18:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'adoration',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    expect($response->json('data.0.end_time'))->toBe('18:00');
    expect($response->json('data.0.start_time'))->toBe('07:00');
});

test('search v2 includes end_time for confession activities', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'confession',
        'activity_type_description' => 'confession',
        'start_time' => '09:00',
        'end_time' => '10:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'confession',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    expect($response->json('data.0.end_time'))->toBe('10:00');
    expect($response->json('data.0.start_time'))->toBe('09:00');
});

test('search v2 returns activities sorted by start time', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '18:00',
    ]);
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '08:00',
    ]);
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '12:00',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
    ]))->assertOk();

    $startTimes = collect($response->json('data'))->pluck('start_time')->all();
    expect($startTimes)->toBe(['08:00', '12:00', '18:00']);
});

test('search v2 shows ongoing confession when searching after start_time', function () {
    $church = Church::factory()->create();

    // Confession from 14:00 - 16:00
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'confession',
        'activity_type_description' => 'confession',
        'start_time' => '14:00',
        'end_time' => '16:00',
    ]);

    // Search at 15:00 — confession is still ongoing, should appear
    $response = getJson(route('search.v2', [
        'type' => 'confession',
        'date' => now()->next('Sunday')->toDateString(),
        'start_time' => '15:00',
    ]))->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.start_time'))->toBe('14:00');
    expect($response->json('data.0.end_time'))->toBe('16:00');
});

test('search v2 shows ongoing adoration when searching after start_time', function () {
    $church = Church::factory()->create();

    // Adoration from 06:00 - 21:00
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'adoration',
        'activity_type_description' => 'adoration',
        'start_time' => '06:00',
        'end_time' => '21:00',
    ]);

    // Search at 10:00 — adoration is still ongoing, should appear
    $response = getJson(route('search.v2', [
        'type' => 'adoration',
        'date' => now()->next('Sunday')->toDateString(),
        'start_time' => '10:00',
    ]))->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.start_time'))->toBe('06:00');
    expect($response->json('data.0.end_time'))->toBe('21:00');
});

test('search v2 hides confession that ended before search time', function () {
    $church = Church::factory()->create();

    // Confession from 09:00 - 10:00
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'confession',
        'activity_type_description' => 'confession',
        'start_time' => '09:00',
        'end_time' => '10:00',
    ]);

    // Search at 10:30 — confession already ended, should NOT appear
    $response = getJson(route('search.v2', [
        'type' => 'confession',
        'date' => now()->next('Sunday')->toDateString(),
        'start_time' => '10:30',
    ]))->assertOk();

    expect($response->json('data'))->toHaveCount(0);
});

test('search v2 still filters mass by start_time only', function () {
    $church = Church::factory()->create();

    // Mass at 08:00 - 09:00
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '08:00',
        'end_time' => '09:00',
    ]);

    // Mass at 10:00
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'type' => 'mass',
        'start_time' => '10:00',
    ]);

    // Search at 08:30 — mass at 08:00 started before search, should NOT appear
    // Mass at 10:00 starts after, should appear
    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => now()->next('Sunday')->toDateString(),
        'start_time' => '08:30',
    ]))->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.start_time'))->toBe('10:00');
});

// =============================================================================
// HOLIDAY SCHEDULE SUPPRESSION TESTS
// =============================================================================

test('search v2 returns no results on a suppressing holiday when church has no liturgical schedule', function () {
    $church = Church::factory()->create();

    // Regular daily mass
    ActivitySchedule::factory()->recycle($church)->create([
        'day_pattern' => DayPattern::Daily,
        'type' => 'mass',
        'start_time' => '07:00',
    ]);

    // Good Friday (suppresses regular schedules)
    // Easter 2025 = April 20, Good Friday = April 18
    Holiday::factory()->goodFriday()->create();

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => '2025-04-18',
    ]))->assertOk();

    expect($response->json('data'))->toHaveCount(0);
});

test('search v2 returns only liturgical results on a suppressing holiday', function () {
    $church = Church::factory()->create();

    // Regular daily mass
    ActivitySchedule::factory()->recycle($church)->create([
        'day_pattern' => DayPattern::Daily,
        'type' => 'mass',
        'start_time' => '07:00',
    ]);

    // Good Friday holiday
    $goodFriday = Holiday::factory()->goodFriday()->create();

    // Create holiday instance
    HolidayInstance::factory()->create([
        'holiday_id' => $goodFriday->id,
        'year' => 2025,
        'date' => '2025-04-18',
    ]);

    // Liturgical schedule for Good Friday
    $litSchedule = LiturgicalSchedule::factory()->create([
        'church_id' => $church->id,
        'holiday_id' => $goodFriday->id,
        'team_id' => $church->team_id,
        'replaces_regular_schedule' => true,
    ]);

    $litSchedule->times()->create([
        'start_time' => '15:00',
        'language' => 'English',
        'type' => 'mass',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => '2025-04-18',
    ]))->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.start_time'))->toBe('15:00');
});

test('search v2 returns one-time activities on a suppressing holiday', function () {
    $church = Church::factory()->create();

    // Regular daily mass (should be suppressed)
    ActivitySchedule::factory()->recycle($church)->create([
        'day_pattern' => DayPattern::Daily,
        'type' => 'mass',
        'start_time' => '07:00',
    ]);

    // Good Friday holiday
    Holiday::factory()->goodFriday()->create();

    // One-time activity on Good Friday
    OneTimeActivity::factory()->create([
        'church_id' => $church->id,
        'date' => '2025-04-18',
        'start_time' => '18:00',
        'type' => 'mass',
        'activity_type_description' => 'Passion of the Lord',
    ]);

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => '2025-04-18',
    ]))->assertOk();

    // Only the one-time activity should appear, not the regular schedule
    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.start_time'))->toBe('18:00');
});

test('search v2 returns regular results on a non-suppressing holiday', function () {
    $church = Church::factory()->create();

    // Regular daily mass
    ActivitySchedule::factory()->recycle($church)->create([
        'day_pattern' => DayPattern::Daily,
        'type' => 'mass',
        'start_time' => '07:00',
    ]);

    // Christmas (does NOT suppress regular schedules)
    Holiday::factory()->christmas()->create();

    $response = getJson(route('search.v2', [
        'type' => 'mass',
        'date' => '2025-12-25',
    ]))->assertOk();

    expect($response->json('data'))->toHaveCount(1);
    expect($response->json('data.0.start_time'))->toBe('07:00');
});
