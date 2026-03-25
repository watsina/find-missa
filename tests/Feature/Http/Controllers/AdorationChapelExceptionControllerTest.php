<?php

use App\Models\Church;
use App\Enums\DayPattern;
use App\Models\AdorationChapel;

use function Pest\Laravel\putJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\deleteJson;

use App\Models\AdorationChapelException;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

describe('Store Adoration Chapel Exception', function () {
    test('user can create an exception for an adoration chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'reason' => 'Chapel cleaning',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-03',
            'day_pattern' => DayPattern::Daily->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertCreated()
            ->assertJsonPath('data.reason', 'Chapel cleaning')
            ->assertJsonPath('data.start_date', '2026-04-01')
            ->assertJsonPath('data.end_date', '2026-04-03')
            ->assertJsonPath('data.day_pattern', DayPattern::Daily->value)
            ->assertJsonPath('data.is_closed', true);

        assertDatabaseHas('adoration_chapel_exceptions', [
            'adoration_chapel_id' => $chapel->id,
            'team_id' => $church->team_id,
            'reason' => 'Chapel cleaning',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-03',
            'day_pattern' => DayPattern::Daily->value,
            'is_closed' => true,
        ]);
    });

    test('user can create an exception with specific hours', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'reason' => 'Easter schedule',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-07',
            'day_pattern' => DayPattern::Daily->value,
            'opens_at' => '08:00',
            'closes_at' => '12:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertCreated()
            ->assertJsonPath('data.opens_at', '08:00')
            ->assertJsonPath('data.closes_at', '12:00')
            ->assertJsonPath('data.is_closed', false);
    });

    test('user can create an exception with 24 hour override', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'reason' => 'Special vigil',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-01',
            'day_pattern' => DayPattern::Daily->value,
            'is_open_24_hours' => true,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertCreated()
            ->assertJsonPath('data.is_open_24_hours', true);
    });

    test('user can create an exception with weekday-only pattern', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
            'day_pattern' => DayPattern::Weekdays->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertCreated()
            ->assertJsonPath('data.day_pattern', DayPattern::Weekdays->value);
    });

    test('user can create an exception without a reason', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-01',
            'day_pattern' => DayPattern::Daily->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertCreated()
            ->assertJsonPath('data.reason', null);
    });

    test('it returns 404 when church has no adoration chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();

        $data = [
            'reason' => 'Cleaning',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-03',
            'day_pattern' => DayPattern::Daily->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertNotFound();
    });

    test('it validates end date is after or equal to start date', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'reason' => 'Cleaning',
            'start_date' => '2026-04-05',
            'end_date' => '2026-04-01',
            'day_pattern' => DayPattern::Daily->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('end_date');
    });

    test('it validates start date is required', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'reason' => 'Cleaning',
            'end_date' => '2026-04-03',
            'day_pattern' => DayPattern::Daily->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('start_date');
    });

    test('it validates day_pattern is required', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-03',
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('day_pattern');
    });

    test('it validates opens_at is required when not 24hr and not closed', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-03',
            'day_pattern' => DayPattern::Daily->value,
            'closes_at' => '17:00',
            'is_open_24_hours' => false,
            'is_closed' => false,
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('opens_at');
    });
});

describe('Update Adoration Chapel Exception', function () {
    test('user can update an exception', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $exception = AdorationChapelException::factory()->create([
            'adoration_chapel_id' => $chapel->id,
            'team_id' => $church->team_id,
            'reason' => 'Old reason',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-03',
        ]);

        $data = [
            'reason' => 'Updated reason',
            'start_date' => '2026-04-05',
            'end_date' => '2026-04-07',
            'day_pattern' => DayPattern::Daily->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = putJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions/{$exception->id}", $data);

        $response->assertSuccessful()
            ->assertJsonPath('data.reason', 'Updated reason')
            ->assertJsonPath('data.start_date', '2026-04-05')
            ->assertJsonPath('data.end_date', '2026-04-07')
            ->assertJsonPath('data.day_pattern', DayPattern::Daily->value);

        assertDatabaseHas('adoration_chapel_exceptions', [
            'id' => $exception->id,
            'reason' => 'Updated reason',
            'start_date' => '2026-04-05',
            'end_date' => '2026-04-07',
            'day_pattern' => DayPattern::Daily->value,
        ]);
    });

    test('it returns 404 when exception does not belong to chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        // Exception belonging to a different chapel
        $otherException = AdorationChapelException::factory()->create();

        $data = [
            'reason' => 'Updated reason',
            'start_date' => '2026-04-05',
            'end_date' => '2026-04-07',
            'day_pattern' => DayPattern::Daily->value,
            'is_closed' => true,
            'is_open_24_hours' => false,
        ];

        $response = putJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions/{$otherException->id}", $data);

        $response->assertNotFound();
    });
});

describe('Delete Adoration Chapel Exception', function () {
    test('user can delete an exception', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $exception = AdorationChapelException::factory()->create([
            'adoration_chapel_id' => $chapel->id,
            'team_id' => $church->team_id,
        ]);

        $response = deleteJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions/{$exception->id}");

        $response->assertNoContent();

        assertDatabaseMissing('adoration_chapel_exceptions', [
            'id' => $exception->id,
        ]);
    });

    test('it returns 404 when exception does not belong to chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $otherException = AdorationChapelException::factory()->create();

        $response = deleteJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions/{$otherException->id}");

        $response->assertNotFound();
    });
});
