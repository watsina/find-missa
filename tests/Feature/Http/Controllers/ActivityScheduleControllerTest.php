<?php

use App\Models\Church;
use App\Enums\DayPattern;
use App\Models\ActivitySchedule;

use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('user can list activity schedules for a church', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    // Create schedules for this church
    ActivitySchedule::factory()->count(3)->create([
        'church_id' => $church->id,
    ]);

    // Create schedule for another church
    ActivitySchedule::factory()->create();

    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
})->group('activity-schedules');

test('user can create a daily activity schedule', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    $data = [
        'church_id' => $church->id,
        'type' => 'mass',
        'activity_type_description' => 'mass',
        'start_time' => '08:00',
        'end_time' => '09:00',
        'language' => 'English',
        'day_pattern' => DayPattern::Daily->value,
    ];

    $response = postJson('/api/v1/activity-schedules', $data);

    $response->assertCreated();

    assertDatabaseHas('activity_schedules', [
        'church_id' => $church->id,
        'day_pattern' => DayPattern::Daily->value,
        'start_time' => '08:00',
    ]);
})->group('activity-schedules');

test('user can create a specific days activity schedule', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    $data = [
        'church_id' => $church->id,
        'type' => 'confession',
        'activity_type_description' => 'confession',
        'start_time' => '17:00',
        'end_time' => '18:00',
        'language' => 'English',
        'day_pattern' => DayPattern::Specific->value,
        'days_of_week' => ['Tuesday', 'Thursday'],
    ];

    $response = postJson('/api/v1/activity-schedules', $data);

    $response->assertCreated();

    $schedule = ActivitySchedule::where('church_id', $church->id)
        ->where('day_pattern', DayPattern::Specific->value)
        ->first();

    expect($schedule)->not->toBeNull();
    expect($schedule->days_of_week)->toBe(['Tuesday', 'Thursday']);
})->group('activity-schedules');

test('user can update an activity schedule', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $schedule = ActivitySchedule::factory()->create([
        'church_id' => $church->id,
        'start_time' => '08:00',
    ]);

    $data = [
        'church_id' => $church->id, // Required by validation but shouldn't change
        'type' => $schedule->type,
        'activity_type_description' => $schedule->activity_type_description,
        'start_time' => '09:00',
        'language' => 'English',
        'day_pattern' => $schedule->day_pattern->value,
        'days_of_week' => $schedule->days_of_week,
    ];

    $response = putJson("/api/v1/activity-schedules/{$schedule->id}", $data);

    $response->assertSuccessful();

    assertDatabaseHas('activity_schedules', [
        'id' => $schedule->id,
        'start_time' => '09:00',
    ]);
})->group('activity-schedules');

test('user can delete an activity schedule', function () {
    $this->actingAsSuperAdminWithTeam();

    $schedule = ActivitySchedule::factory()->create();

    $response = deleteJson("/api/v1/activity-schedules/{$schedule->id}");

    $response->assertSuccessful();

    assertDatabaseMissing('activity_schedules', [
        'id' => $schedule->id,
    ]);
})->group('activity-schedules');

test('creating a confession schedule without end_time returns validation error', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    $data = [
        'church_id' => $church->id,
        'type' => 'confession',
        'activity_type_description' => 'confession',
        'start_time' => '14:00',
        'language' => 'English',
        'day_pattern' => DayPattern::Daily->value,
    ];

    $response = postJson('/api/v1/activity-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['end_time']);
})->group('activity-schedules');

test('creating an adoration schedule without end_time returns validation error', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    $data = [
        'church_id' => $church->id,
        'type' => 'adoration',
        'activity_type_description' => 'adoration',
        'start_time' => '09:00',
        'language' => 'English',
        'day_pattern' => DayPattern::Daily->value,
    ];

    $response = postJson('/api/v1/activity-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['end_time']);
})->group('activity-schedules');

test('matches_day filter returns daily schedules for any day', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    $dailySchedule = ActivitySchedule::factory()->daily()->create([
        'church_id' => $church->id,
        'start_time' => '07:00',
    ]);

    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Monday");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $dailySchedule->id);
})->group('activity-schedules');

test('matches_day filter returns weekday schedules for weekdays only', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    ActivitySchedule::factory()->weekdays()->create([
        'church_id' => $church->id,
        'start_time' => '07:30',
    ]);

    // Weekday should match
    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Wednesday");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');

    // Weekend should not match
    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Saturday");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
})->group('activity-schedules');

test('matches_day filter returns sunday schedules for sunday only', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    ActivitySchedule::factory()->sunday()->create([
        'church_id' => $church->id,
        'start_time' => '09:00',
    ]);

    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Sunday");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Monday");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
})->group('activity-schedules');

test('matches_day filter returns saturday schedules for saturday only', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    ActivitySchedule::factory()->saturday()->create([
        'church_id' => $church->id,
        'start_time' => '16:00',
    ]);

    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Saturday");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');

    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Sunday");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
})->group('activity-schedules');

test('matches_day filter returns specific day schedules when day is included', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    ActivitySchedule::factory()->specificDays(['Tuesday', 'Thursday'])->create([
        'church_id' => $church->id,
        'type' => 'confession',
        'activity_type_description' => 'confession',
        'start_time' => '15:00',
        'end_time' => '16:00',
    ]);

    // Included day should match
    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Thursday");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');

    // Not-included day should not match
    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Wednesday");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
})->group('activity-schedules');

test('matches_day filter excludes monthly-nth schedules', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    // First Friday mass — should NOT appear in matches_day results
    ActivitySchedule::factory()->monthlyNth('Friday')->create([
        'church_id' => $church->id,
        'start_time' => '18:00',
    ]);

    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Friday");

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
})->group('activity-schedules');

test('matches_day filter returns multiple matching schedules across patterns', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    // Daily mass at 7:00 — should match Sunday
    ActivitySchedule::factory()->daily()->create([
        'church_id' => $church->id,
        'start_time' => '07:00',
    ]);

    // Sunday mass at 9:00 — should match Sunday
    ActivitySchedule::factory()->sunday()->create([
        'church_id' => $church->id,
        'start_time' => '09:00',
    ]);

    // Specific days including Sunday — should match Sunday
    ActivitySchedule::factory()->specificDays(['Sunday', 'Wednesday'])->create([
        'church_id' => $church->id,
        'type' => 'adoration',
        'activity_type_description' => 'adoration',
        'start_time' => '14:00',
        'end_time' => '17:00',
    ]);

    // Saturday mass — should NOT match Sunday
    ActivitySchedule::factory()->saturday()->create([
        'church_id' => $church->id,
        'start_time' => '17:00',
    ]);

    // Monthly-nth First Sunday — should NOT match (excluded by design)
    ActivitySchedule::factory()->monthlyNth('Sunday')->create([
        'church_id' => $church->id,
        'start_time' => '11:00',
    ]);

    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=Sunday");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
})->group('activity-schedules');

test('matches_day filter is case insensitive', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    ActivitySchedule::factory()->sunday()->create([
        'church_id' => $church->id,
    ]);

    $response = getJson("/api/v1/activity-schedules?church_id={$church->id}&matches_day=sunday");

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
})->group('activity-schedules');

test('creating a mass schedule without end_time succeeds', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    $data = [
        'church_id' => $church->id,
        'type' => 'mass',
        'activity_type_description' => 'mass',
        'start_time' => '10:00',
        'language' => 'English',
        'day_pattern' => DayPattern::Daily->value,
    ];

    $response = postJson('/api/v1/activity-schedules', $data);

    $response->assertCreated();

    assertDatabaseHas('activity_schedules', [
        'church_id' => $church->id,
        'type' => 'mass',
        'start_time' => '10:00',
        'end_time' => null,
    ]);
})->group('activity-schedules');
