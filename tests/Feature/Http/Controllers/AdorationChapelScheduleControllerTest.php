<?php

use App\Models\Church;
use App\Enums\DayPattern;
use App\Models\AdorationChapel;

use function Pest\Laravel\putJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\deleteJson;

use App\Models\AdorationChapelSchedule;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

describe('Store Adoration Chapel Schedule', function () {
    test('user can create a schedule for an adoration chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '06:00',
            'closes_at' => '22:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated()
            ->assertJsonPath('data.day_pattern', DayPattern::Weekdays->value)
            ->assertJsonPath('data.opens_at', '06:00')
            ->assertJsonPath('data.closes_at', '22:00');

        assertDatabaseHas('adoration_chapel_schedules', [
            'adoration_chapel_id' => $chapel->id,
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '06:00',
            'closes_at' => '22:00',
        ]);
    });

    test('user can create a 24-hour schedule', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Sunday->value,
            'is_open_24_hours' => true,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated()
            ->assertJsonPath('data.is_open_24_hours', true);
    });

    test('user can create a closed schedule', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Saturday->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated()
            ->assertJsonPath('data.is_closed', true);
    });

    test('user can create a schedule with day details', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '06:00',
            'closes_at' => '20:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
            'day_details' => [
                [
                    'day_of_week' => 'Monday',
                    'opens_at' => '07:00',
                    'closes_at' => '18:00',
                    'is_open_24_hours' => false,
                    'is_closed' => false,
                ],
                [
                    'day_of_week' => 'Friday',
                    'is_open_24_hours' => true,
                    'is_closed' => false,
                ],
            ],
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated();

        assertDatabaseHas('adoration_chapel_schedule_details', [
            'day_of_week' => 'Monday',
            'opens_at' => '07:00',
        ]);

        assertDatabaseHas('adoration_chapel_schedule_details', [
            'day_of_week' => 'Friday',
            'is_open_24_hours' => true,
        ]);
    });

    test('user can create a schedule with specific days', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Specific->value,
            'days_of_week' => ['Monday', 'Wednesday', 'Friday'],
            'opens_at' => '08:00',
            'closes_at' => '17:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated()
            ->assertJsonPath('data.day_pattern', DayPattern::Specific->value);
    });

    test('it returns 404 when church has no adoration chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();

        $data = [
            'day_pattern' => DayPattern::Daily->value,
            'is_open_24_hours' => true,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertNotFound();
    });

    test('monthly-nth day pattern is not allowed', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::MonthlyNth->value,
            'opens_at' => '08:00',
            'closes_at' => '17:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('day_pattern');
    });
});

describe('Store Adoration Chapel Schedule - Required Times Validation', function () {
    test('opens_at is required when not 24hr and not closed', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Daily->value,
            'closes_at' => '22:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('opens_at');
    });

    test('closes_at is required when not 24hr and not closed', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Daily->value,
            'opens_at' => '06:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('closes_at');
    });

    test('times are not required when 24hr is true', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Daily->value,
            'is_open_24_hours' => true,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated();
    });

    test('times are not required when closed is true', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Daily->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated();
    });

    test('days_of_week is required for specific day pattern', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Specific->value,
            'opens_at' => '08:00',
            'closes_at' => '17:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('days_of_week');
    });
});

describe('Store Adoration Chapel Schedule - Overlap Validation', function () {
    test('it rejects a schedule that overlaps with existing daily 24hr schedule', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        // Existing: daily, 24hr
        AdorationChapelSchedule::factory()->daily()->open24Hours()->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        // Try to add another schedule on weekdays
        $data = [
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '08:00',
            'closes_at' => '17:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('day_pattern');
    });

    test('it rejects overlapping time ranges on the same day', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        // Existing: weekdays 08:00-17:00
        AdorationChapelSchedule::factory()->weekdays()->hours('08:00', '17:00')->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        // Try to add weekdays 10:00-20:00 (overlaps)
        $data = [
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '10:00',
            'closes_at' => '20:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('day_pattern');
    });

    test('it allows non-overlapping time ranges on the same day', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        // Existing: weekdays 06:00-12:00
        AdorationChapelSchedule::factory()->weekdays()->hours('06:00', '12:00')->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        // Add weekdays 14:00-22:00 (no overlap)
        $data = [
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '14:00',
            'closes_at' => '22:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated();
    });

    test('it allows schedules on different days', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        // Existing: weekdays 06:00-22:00
        AdorationChapelSchedule::factory()->weekdays()->hours('06:00', '22:00')->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        // Add saturday (different days, no overlap)
        $data = [
            'day_pattern' => DayPattern::Saturday->value,
            'opens_at' => '08:00',
            'closes_at' => '18:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated();
    });

    test('closed schedule does not create overlap', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        // Existing: daily closed
        AdorationChapelSchedule::factory()->daily()->closed()->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        // Add weekdays schedule — should be allowed since existing is closed
        $data = [
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '08:00',
            'closes_at' => '17:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules", $data);

        $response->assertCreated();
    });
});

describe('Update Adoration Chapel Schedule', function () {
    test('user can update a schedule', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $schedule = AdorationChapelSchedule::factory()->daily()->hours('06:00', '22:00')->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        $data = [
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '08:00',
            'closes_at' => '20:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = putJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules/{$schedule->id}", $data);

        $response->assertSuccessful()
            ->assertJsonPath('data.day_pattern', DayPattern::Weekdays->value)
            ->assertJsonPath('data.opens_at', '08:00')
            ->assertJsonPath('data.closes_at', '20:00');

        assertDatabaseHas('adoration_chapel_schedules', [
            'id' => $schedule->id,
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '08:00',
            'closes_at' => '20:00',
        ]);
    });

    test('it returns 404 when schedule does not belong to chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        // Schedule belonging to a different chapel
        $otherSchedule = AdorationChapelSchedule::factory()->create();

        $data = [
            'day_pattern' => DayPattern::Daily->value,
            'opens_at' => '08:00',
            'closes_at' => '20:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = putJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules/{$otherSchedule->id}", $data);

        $response->assertNotFound();
    });

    test('overlap validation excludes the schedule being updated', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $schedule = AdorationChapelSchedule::factory()->daily()->hours('06:00', '22:00')->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        // Updating the same schedule to different hours should work (no self-overlap)
        $data = [
            'day_pattern' => DayPattern::Daily->value,
            'opens_at' => '08:00',
            'closes_at' => '20:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = putJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules/{$schedule->id}", $data);

        $response->assertSuccessful();
    });

    test('overlap validation catches overlap with other schedules on update', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        // Existing schedule 1: weekdays 06:00-12:00
        AdorationChapelSchedule::factory()->weekdays()->hours('06:00', '12:00')->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        // Existing schedule 2: saturday 08:00-18:00
        $saturdaySchedule = AdorationChapelSchedule::factory()->saturday()->hours('08:00', '18:00')->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        // Try to update saturday schedule to weekdays with overlapping time
        $data = [
            'day_pattern' => DayPattern::Weekdays->value,
            'opens_at' => '10:00',
            'closes_at' => '20:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = putJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules/{$saturdaySchedule->id}", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('day_pattern');
    });
});

describe('Delete Adoration Chapel Schedule', function () {
    test('user can delete a schedule', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $schedule = AdorationChapelSchedule::factory()->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        $response = deleteJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules/{$schedule->id}");

        $response->assertNoContent();

        assertDatabaseMissing('adoration_chapel_schedules', [
            'id' => $schedule->id,
        ]);
    });

    test('it returns 404 when schedule does not belong to chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $otherSchedule = AdorationChapelSchedule::factory()->create();

        $response = deleteJson("/api/v1/churches/{$church->id}/adoration-chapel/schedules/{$otherSchedule->id}");

        $response->assertNotFound();
    });
});
