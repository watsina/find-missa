<?php

use App\Models\Church;
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
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertCreated()
            ->assertJsonPath('data.reason', 'Chapel cleaning')
            ->assertJsonPath('data.start_date', '2026-04-01')
            ->assertJsonPath('data.end_date', '2026-04-03');

        assertDatabaseHas('adoration_chapel_exceptions', [
            'adoration_chapel_id' => $chapel->id,
            'team_id' => $church->team_id,
            'reason' => 'Chapel cleaning',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-03',
        ]);
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
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('start_date');
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
        ];

        $response = putJson("/api/v1/churches/{$church->id}/adoration-chapel/exceptions/{$exception->id}", $data);

        $response->assertSuccessful()
            ->assertJsonPath('data.reason', 'Updated reason')
            ->assertJsonPath('data.start_date', '2026-04-05')
            ->assertJsonPath('data.end_date', '2026-04-07');

        assertDatabaseHas('adoration_chapel_exceptions', [
            'id' => $exception->id,
            'reason' => 'Updated reason',
            'start_date' => '2026-04-05',
            'end_date' => '2026-04-07',
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
