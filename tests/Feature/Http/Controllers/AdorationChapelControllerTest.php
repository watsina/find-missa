<?php

use App\Models\Church;
use App\Models\AdorationChapel;

use function Pest\Laravel\getJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\deleteJson;

use App\Models\AdorationChapelSchedule;
use App\Models\AdorationChapelException;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

describe('Show Adoration Chapel', function () {
    test('user can view adoration chapel for a church', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
            'name' => 'Divine Mercy Chapel',
            'notes' => 'Open to all',
        ]);

        AdorationChapelSchedule::factory()->daily()->create([
            'adoration_chapel_id' => $chapel->id,
            'opens_at' => '06:00',
            'closes_at' => '22:00',
        ]);

        $response = getJson("/api/v1/churches/{$church->id}/adoration-chapel");

        $response->assertSuccessful()
            ->assertJsonPath('data.name', 'Divine Mercy Chapel')
            ->assertJsonPath('data.notes', 'Open to all')
            ->assertJsonPath('data.church_id', $church->id);
    });

    test('it returns 404 when church has no adoration chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();

        $response = getJson("/api/v1/churches/{$church->id}/adoration-chapel");

        $response->assertNotFound();
    });
});

describe('Store Adoration Chapel', function () {
    test('user can create an adoration chapel for a church', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();

        $data = [
            'name' => 'Perpetual Adoration Chapel',
            'notes' => 'Bring your own rosary',
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel", $data);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Perpetual Adoration Chapel');

        assertDatabaseHas('adoration_chapels', [
            'church_id' => $church->id,
            'team_id' => $church->team_id,
            'name' => 'Perpetual Adoration Chapel',
        ]);
    });

    test('user cannot create a second adoration chapel for a church', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        $data = [
            'name' => 'Another Chapel',
        ];

        $response = postJson("/api/v1/churches/{$church->id}/adoration-chapel", $data);

        $response->assertUnprocessable();
    });
});

describe('Update Adoration Chapel', function () {
    test('user can update an adoration chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
            'name' => 'Old Name',
        ]);

        $data = [
            'name' => 'New Chapel Name',
            'notes' => 'Updated notes',
        ];

        $response = putJson("/api/v1/churches/{$church->id}/adoration-chapel", $data);

        $response->assertSuccessful()
            ->assertJsonPath('data.name', 'New Chapel Name');

        assertDatabaseHas('adoration_chapels', [
            'id' => $chapel->id,
            'name' => 'New Chapel Name',
            'notes' => 'Updated notes',
        ]);
    });

    test('it returns 404 when updating nonexistent chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();

        $data = [
            'name' => 'New Name',
        ];

        $response = putJson("/api/v1/churches/{$church->id}/adoration-chapel", $data);

        $response->assertNotFound();
    });
});

describe('Delete Adoration Chapel', function () {
    test('user can delete an adoration chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create([
            'church_id' => $church->id,
            'team_id' => $church->team_id,
        ]);

        AdorationChapelSchedule::factory()->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        AdorationChapelException::factory()->create([
            'adoration_chapel_id' => $chapel->id,
        ]);

        $response = deleteJson("/api/v1/churches/{$church->id}/adoration-chapel");

        $response->assertNoContent();

        assertDatabaseMissing('adoration_chapels', [
            'id' => $chapel->id,
        ]);

        assertDatabaseMissing('adoration_chapel_schedules', [
            'adoration_chapel_id' => $chapel->id,
        ]);

        assertDatabaseMissing('adoration_chapel_exceptions', [
            'adoration_chapel_id' => $chapel->id,
        ]);
    });

    test('it returns 404 when deleting nonexistent chapel', function () {
        $this->actingAsSuperAdminWithTeam();

        $church = Church::factory()->create();

        $response = deleteJson("/api/v1/churches/{$church->id}/adoration-chapel");

        $response->assertNotFound();
    });
});
