<?php

use App\Models\Church;
use App\Models\Holiday;
use App\Models\LiturgicalSchedule;

use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\deleteJson;

use App\Models\LiturgicalScheduleTime;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('user can list liturgical schedules for a church', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    // Create schedules for this church
    LiturgicalSchedule::factory()->count(3)->create([
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
    ]);

    // Create schedule for another church
    LiturgicalSchedule::factory()->create();

    $response = getJson("/api/v1/liturgical-schedules?church_id={$church->id}");

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
})->group('liturgical-schedules');

test('user can filter liturgical schedules by holiday', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $christmasHoliday = Holiday::factory()->create(['name' => 'Christmas']);
    $easterHoliday = Holiday::factory()->create(['name' => 'Easter']);

    // Create schedules for Christmas
    LiturgicalSchedule::factory()->count(2)->create([
        'church_id' => $church->id,
        'holiday_id' => $christmasHoliday->id,
    ]);

    // Create schedule for Easter
    LiturgicalSchedule::factory()->create([
        'church_id' => $church->id,
        'holiday_id' => $easterHoliday->id,
    ]);

    $response = getJson("/api/v1/liturgical-schedules?church_id={$church->id}&holiday_id={$christmasHoliday->id}");

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
})->group('liturgical-schedules');

test('user can filter liturgical schedules by year', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    // Create a recurring schedule (year is null)
    LiturgicalSchedule::factory()->recurring()->create([
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
    ]);

    // Create schedule for 2025
    LiturgicalSchedule::factory()->forYear(2025)->create([
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
    ]);

    // Create schedule for 2026
    LiturgicalSchedule::factory()->forYear(2026)->create([
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
    ]);

    // Filtering by year should return recurring schedules + specific year schedules
    $response = getJson("/api/v1/liturgical-schedules?church_id={$church->id}&year=2025");

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data'); // recurring + 2025 specific
})->group('liturgical-schedules');

test('user can create a liturgical schedule with times', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'year' => null, // recurring
        'replaces_regular_schedule' => true,
        'notes' => 'Special Christmas masses',
        'times' => [
            [
                'type' => 'mass',
                'start_time' => '00:00',
                'end_time' => '01:00',
                'language' => 'English',
            ],
            [
                'type' => 'mass',
                'start_time' => '10:00',
                'end_time' => '11:00',
                'language' => 'Spanish',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertCreated();

    assertDatabaseHas('liturgical_schedules', [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'year' => null,
        'replaces_regular_schedule' => true,
        'notes' => 'Special Christmas masses',
    ]);

    // Verify times were created
    $schedule = LiturgicalSchedule::where('church_id', $church->id)->first();
    expect($schedule->times)->toHaveCount(2);
})->group('liturgical-schedules');

test('user can create a year-specific liturgical schedule', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'year' => 2026,
        'replaces_regular_schedule' => false,
        'times' => [
            [
                'type' => 'mass',
                'start_time' => '08:00',
                'language' => 'English',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertCreated();

    assertDatabaseHas('liturgical_schedules', [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'year' => 2026,
        'replaces_regular_schedule' => false,
    ]);
})->group('liturgical-schedules');

test('user cannot create liturgical schedule without times', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times']);
})->group('liturgical-schedules');

test('user can view a specific liturgical schedule', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $schedule = LiturgicalSchedule::factory()->create([
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
    ]);

    // Create some times
    LiturgicalScheduleTime::factory()->count(2)->create([
        'liturgical_schedule_id' => $schedule->id,
    ]);

    $response = getJson("/api/v1/liturgical-schedules/{$schedule->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $schedule->id)
        ->assertJsonPath('data.church_id', $church->id)
        ->assertJsonPath('data.holiday_id', $holiday->id)
        ->assertJsonCount(2, 'data.times');
})->group('liturgical-schedules');

test('user can update a liturgical schedule', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $schedule = LiturgicalSchedule::factory()->create([
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'notes' => 'Original notes',
        'replaces_regular_schedule' => true,
    ]);

    // Create initial times
    LiturgicalScheduleTime::factory()->count(2)->create([
        'liturgical_schedule_id' => $schedule->id,
    ]);

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'notes' => 'Updated notes',
        'replaces_regular_schedule' => false,
        'times' => [
            [
                'type' => 'mass',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'language' => 'French',
            ],
        ],
    ];

    $response = putJson("/api/v1/liturgical-schedules/{$schedule->id}", $data);

    $response->assertSuccessful();

    assertDatabaseHas('liturgical_schedules', [
        'id' => $schedule->id,
        'notes' => 'Updated notes',
        'replaces_regular_schedule' => false,
    ]);

    // Verify times were replaced
    $schedule->refresh();
    expect($schedule->times)->toHaveCount(1);
    expect($schedule->times->first()->language)->toBe('French');
})->group('liturgical-schedules');

test('user can update liturgical schedule without changing times', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $schedule = LiturgicalSchedule::factory()->create([
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'notes' => 'Original notes',
    ]);

    // Create initial times
    LiturgicalScheduleTime::factory()->count(3)->create([
        'liturgical_schedule_id' => $schedule->id,
    ]);

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'notes' => 'Updated notes',
        // No times array - should preserve existing times
    ];

    $response = putJson("/api/v1/liturgical-schedules/{$schedule->id}", $data);

    $response->assertSuccessful();

    // Times should be preserved
    $schedule->refresh();
    expect($schedule->times)->toHaveCount(3);
})->group('liturgical-schedules');

test('user can delete a liturgical schedule', function () {
    $this->actingAsSuperAdminWithTeam();

    $schedule = LiturgicalSchedule::factory()->create();

    // Create some times
    LiturgicalScheduleTime::factory()->count(2)->create([
        'liturgical_schedule_id' => $schedule->id,
    ]);

    $response = deleteJson("/api/v1/liturgical-schedules/{$schedule->id}");

    $response->assertNoContent();

    assertDatabaseMissing('liturgical_schedules', [
        'id' => $schedule->id,
    ]);

    // Times should also be deleted
    assertDatabaseMissing('liturgical_schedule_times', [
        'liturgical_schedule_id' => $schedule->id,
    ]);
})->group('liturgical-schedules');

test('validation fails for invalid time format', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'mass',
                'start_time' => 'invalid-time',
                'language' => 'English',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times.0.start_time']);
})->group('liturgical-schedules');

test('validation fails for invalid activity type', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'invalid-type',
                'start_time' => '10:00',
                'language' => 'English',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times.0.type']);
})->group('liturgical-schedules');

test('validation fails when activity_type_description does not match type', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'confession',
                'activity_type_description' => 'mass', // mass description is invalid for confession type
                'start_time' => '10:00',
                'end_time' => '11:00',
                'language' => 'English',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times.0.activity_type_description']);
})->group('liturgical-schedules');

test('valid activity_type_description matching type passes validation', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'mass',
                'activity_type_description' => 'easter-vigil-mass',
                'start_time' => '20:00',
                'language' => 'English',
            ],
            [
                'type' => 'confession',
                'activity_type_description' => 'confession',
                'start_time' => '15:00',
                'end_time' => '16:00',
                'language' => 'English',
            ],
            [
                'type' => 'adoration',
                'activity_type_description' => 'exposition',
                'start_time' => '12:00',
                'end_time' => '13:00',
                'language' => 'English',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertCreated();

    $schedule = LiturgicalSchedule::where('church_id', $church->id)->first();
    expect($schedule->times)->toHaveCount(3);
})->group('liturgical-schedules');

test('validation fails when end_time is missing for confession type', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'confession',
                'activity_type_description' => 'confession',
                'start_time' => '10:00',
                'end_time' => '',
                'language' => 'English',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times.0.end_time']);
})->group('liturgical-schedules');

test('validation fails when end_time is missing for adoration type', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'adoration',
                'activity_type_description' => 'adoration',
                'start_time' => '10:00',
                'end_time' => '',
                'language' => 'English',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times.0.end_time']);
})->group('liturgical-schedules');

test('validation fails when end_time is before start_time', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'mass',
                'start_time' => '10:00',
                'end_time' => '09:00',
                'language' => 'English',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times.0.end_time']);
})->group('liturgical-schedules');

test('validation fails when start_time is missing', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'mass',
                'start_time' => '',
                'language' => 'English',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times.0.start_time']);
})->group('liturgical-schedules');

test('validation fails when language is missing', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'mass',
                'start_time' => '10:00',
                'language' => '',
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times.0.language']);
})->group('liturgical-schedules');

test('validation errors target correct indices for multiple time entries', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $data = [
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
        'times' => [
            [
                'type' => 'mass',
                'start_time' => '10:00',
                'language' => 'English',
            ],
            [
                'type' => 'confession',
                'activity_type_description' => 'mass', // wrong description for confession
                'start_time' => '14:00',
                'end_time' => '', // missing end_time for confession
                'language' => 'English',
            ],
            [
                'type' => 'mass',
                'start_time' => '', // missing start_time
                'language' => '',   // missing language
            ],
        ],
    ];

    $response = postJson('/api/v1/liturgical-schedules', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'times.1.activity_type_description',
            'times.1.end_time',
            'times.2.start_time',
            'times.2.language',
        ])
        ->assertJsonMissingValidationErrors([
            'times.0.type',
            'times.0.start_time',
            'times.0.language',
        ]);
})->group('liturgical-schedules');

test('update validation fails when activity_type_description does not match type', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create();

    $schedule = LiturgicalSchedule::factory()->create([
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
    ]);

    LiturgicalScheduleTime::factory()->create([
        'liturgical_schedule_id' => $schedule->id,
    ]);

    $data = [
        'times' => [
            [
                'type' => 'adoration',
                'activity_type_description' => 'mass', // invalid for adoration
                'start_time' => '10:00',
                'end_time' => '11:00',
                'language' => 'English',
            ],
        ],
    ];

    $response = putJson("/api/v1/liturgical-schedules/{$schedule->id}", $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['times.0.activity_type_description']);
})->group('liturgical-schedules');

test('response includes holiday information', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create(['name' => 'Christmas']);

    $schedule = LiturgicalSchedule::factory()->create([
        'church_id' => $church->id,
        'holiday_id' => $holiday->id,
    ]);

    LiturgicalScheduleTime::factory()->create([
        'liturgical_schedule_id' => $schedule->id,
    ]);

    $response = getJson("/api/v1/liturgical-schedules/{$schedule->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.holiday.name', 'Christmas');
})->group('liturgical-schedules');
