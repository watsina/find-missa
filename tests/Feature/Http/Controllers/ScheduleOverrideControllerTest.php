<?php

use App\Models\Church;
use App\Enums\ScheduleScope;
use App\Enums\ScheduleAction;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;

use App\Models\ScheduleOverride;

use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

test('user can list schedule overrides for a church', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    // Create overrides for this church
    ScheduleOverride::factory()->count(3)->forChurch($church)->create();

    // Create override for another church
    ScheduleOverride::factory()->create();

    $response = get(route('schedule-overrides.index', [
        'scope_type' => ScheduleScope::Church->value,
        'scope_id' => $church->id,
    ]));

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
})->group('schedule-overrides');

test('user can create a schedule override', function () {
    $this->actingAsSuperAdminWithTeam();

    $church = Church::factory()->create();

    $data = [
        'scope_type' => ScheduleScope::Church->value,
        'scope_id' => $church->id,
        'name' => 'Christmas Week Closure',
        'start_date' => '2025-12-24',
        'end_date' => '2025-12-26',
        'action' => ScheduleAction::Exclude->value,
        'is_active' => true,
    ];

    $response = post(route('schedule-overrides.store'), $data);

    $response->assertCreated();

    $override = ScheduleOverride::where('scope_id', $church->id)
        ->where('name', 'Christmas Week Closure')
        ->first();

    expect($override)->not->toBeNull();
    expect($override->action->value)->toBe(ScheduleAction::Exclude->value);
})->group('schedule-overrides');

test('user can view a schedule override', function () {
    $this->actingAsSuperAdminWithTeam();

    $override = ScheduleOverride::factory()->create();

    $response = get(route('schedule-overrides.show', $override));

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $override->id);
})->group('schedule-overrides');

test('user can update a schedule override', function () {
    $this->actingAsSuperAdminWithTeam();

    $override = ScheduleOverride::factory()->create([
        'name' => 'Original Name',
    ]);

    $data = [
        'scope_type' => $override->scope_type->value,
        'scope_id' => $override->scope_id,
        'name' => 'Updated Name',
        'start_date' => $override->start_date,
        'end_date' => $override->end_date,
        'action' => $override->action->value,
        'is_active' => true,
    ];

    $response = put(route('schedule-overrides.update', $override), $data);

    $response->assertSuccessful();

    $override->refresh();

    expect($override->name)->toBe('Updated Name');
})->group('schedule-overrides');

test('admin can delete a schedule override', function () {
    $this->actingAsSuperAdminWithTeam();

    $override = ScheduleOverride::factory()->create();

    $response = delete(route('schedule-overrides.destroy', $override));

    $response->assertNoContent();

    assertModelMissing($override);
})->group('schedule-overrides');

test('index can filter by active status', function () {
    $this->actingAsSuperAdminWithTeam();

    $activeOverride = ScheduleOverride::factory()->create(['is_active' => true]);
    $inactiveOverride = ScheduleOverride::factory()->inactive()->create();

    $response = get(route('schedule-overrides.index', ['is_active' => 'true']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $activeOverride->id);
})->group('schedule-overrides');

test('index can filter by date coverage', function () {
    $this->actingAsSuperAdminWithTeam();

    $override1 = ScheduleOverride::factory()->forDateRange('2025-04-01', '2025-04-10')->create();
    $override2 = ScheduleOverride::factory()->forDateRange('2025-05-01', '2025-05-10')->create();

    $response = get(route('schedule-overrides.index', ['date' => '2025-04-05']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $override1->id);
})->group('schedule-overrides');
