<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ActivityTime;
use App\Models\SchedulePeriod;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelExists;
use function PHPUnit\Framework\assertEquals;
use function Pest\Laravel\assertModelMissing;

test('index behaves as expected', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTimes = ActivityTime::factory()->count(3)->create();

    $response = get(route('activity-times.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTimeController::class,
        'store',
        \App\Http\Requests\ActivityTimeRequest::class
    );

test('store saves', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $data = ActivityTime::factory()->raw();

    post(route('activity-times.store'), $data)
        ->assertCreated()
        ->assertJsonStructure([]);

    $activityTimes = ActivityTime::query()
        ->where('type', $data['type'])
        ->where('start_time', $data['start_time'])
        ->where('end_time', $data['end_time'])
        ->where('language', $data['language'])
        ->where('occurrence', $data['occurrence'])
        ->get();
    expect($activityTimes)->toHaveCount(1);
});

test('user can save multiple weekly records', function () {
    $this->actingAsSuperAdminWithTeam();
    // we have cases where multiple days have the same details
    $data = ActivityTime::factory()->weekly()->raw(['day_of_week' => null]);

    post(route('activity-times.store'), [...$data, 'days_of_week' => ['Monday', 'Tuesday', 'Wednesday']])
        ->assertCreated()
        ->assertJsonStructure([]);

    assertEquals(3, ActivityTime::count());
});

test('user can save multiple bulk time records', function () {
    $this->actingAsSuperAdminWithTeam();
    // we have cases where multiple days have the same details
    $data = ActivityTime::factory()->weekly()->raw();

    post(route('activity-times.store'), [
        ...$data,
        'start_time' => null,
        'starting_times' => ['07:00', '13:00', '18:00'],
    ])
        ->assertCreated()
        ->assertJsonStructure([]);

    assertEquals(3, ActivityTime::count());
});

test('user can save multiple bulk time records with multiply weekly records', function () {
    $this->actingAsSuperAdminWithTeam();
    // we have cases where multiple days have the same details
    $data = ActivityTime::factory()->weekly()->raw(['day_of_week' => null]);

    post(route('activity-times.store'), [
        ...$data,
        'days_of_week' => ['Monday', 'Tuesday', 'Wednesday'],
        'start_time' => null,
        'starting_times' => ['07:00', '13:00', '18:00'],
    ])
        ->assertCreated()
        ->assertJsonStructure([]);

    assertEquals(9, ActivityTime::count());
});

test('show behaves as expected', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create();

    $response = get(route('activity-times.show', $activityTime));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTimeController::class,
        'update',
        \App\Http\Requests\ActivityTimeRequest::class
    );

test('update behaves as expected', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create();
    $data = ActivityTime::factory()->raw();

    put(route('activity-times.update', $activityTime), [
        'type' => $data['type'],
        'activity_type_description' => $data['activity_type_description'],
        'start_time' => $data['start_time'],
        'end_time' => $data['end_time'],
        'language' => $data['language'],
        'occurrence' => $data['occurrence'],
        'date' => $data['date'],
        'day_of_week' => $data['day_of_week'],
    ])
        ->assertOk()
        ->assertJsonStructure([]);

    $activityTime->refresh();

    expect($data['type'])->toEqual($activityTime->type);
    expect($data['start_time'])->toEqual($activityTime->start_time);
    expect($data['end_time'])->toEqual($activityTime->end_time);
    expect($data['language'])->toEqual($activityTime->language);
    expect($data['occurrence'])->toEqual($activityTime->occurrence);
});

test('admin can delete an activity time.', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime = ActivityTime::factory()->create();

    $response = delete(route('activity-times.destroy', $activityTime));

    $response->assertNoContent();

    assertModelMissing($activityTime);
});

test('a non admin cannot delete an activity time.', function (): void {
    $this->actingAsUserWithTeam();

    $activityTime = ActivityTime::factory()->create();

    delete(route('activity-times.destroy', $activityTime))
        ->assertForbidden();

    assertModelExists($activityTime);
});

// Schedule Period Integration Tests

test('index can filter by schedule_period_id', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $schedulePeriod = SchedulePeriod::factory()->create();
    $activityTimeWithPeriod = ActivityTime::factory()->create([
        'schedule_period_id' => $schedulePeriod->id,
    ]);
    $activityTimeWithoutPeriod = ActivityTime::factory()->create([
        'schedule_period_id' => null,
    ]);

    $response = get(route('activity-times.index', ['schedule_period_id' => $schedulePeriod->id]));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $activityTimeWithPeriod->id);
});

test('index can filter for activity times without schedule period', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $schedulePeriod = SchedulePeriod::factory()->create();
    ActivityTime::factory()->create([
        'schedule_period_id' => $schedulePeriod->id,
    ]);
    $activityTimeWithoutPeriod = ActivityTime::factory()->create([
        'schedule_period_id' => null,
    ]);

    $response = get(route('activity-times.index', ['schedule_period_id' => 'null']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $activityTimeWithoutPeriod->id);
});

test('index can filter by effective_on date', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $effectiveActivity = ActivityTime::factory()->create([
        'effective_from' => '2024-01-01',
        'effective_until' => '2024-12-31',
    ]);
    $notYetEffectiveActivity = ActivityTime::factory()->create([
        'effective_from' => '2025-06-01',
        'effective_until' => '2025-12-31',
    ]);
    $alwaysEffectiveActivity = ActivityTime::factory()->create([
        'effective_from' => null,
        'effective_until' => null,
    ]);

    $response = get(route('activity-times.index', ['effective_on' => '2024-06-15']));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');

    $ids = collect($response->json('data'))->pluck('id')->toArray();
    expect($ids)->toContain($effectiveActivity->id);
    expect($ids)->toContain($alwaysEffectiveActivity->id);
    expect($ids)->not->toContain($notYetEffectiveActivity->id);
});

test('store can create activity time with schedule period', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $schedulePeriod = SchedulePeriod::factory()->create();
    $data = ActivityTime::factory()->raw([
        'schedule_period_id' => $schedulePeriod->id,
        'effective_from' => '2024-01-01',
        'effective_until' => '2024-03-31',
    ]);

    $response = post(route('activity-times.store'), $data);

    $response->assertCreated();

    $activityTime = ActivityTime::latest('id')->first();
    expect($activityTime->schedule_period_id)->toBe($schedulePeriod->id);
    expect($activityTime->effective_from->toDateString())->toBe('2024-01-01');
    expect($activityTime->effective_until->toDateString())->toBe('2024-03-31');
});

test('store validates effective_until is after effective_from', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $data = ActivityTime::factory()->raw([
        'effective_from' => '2024-06-01',
        'effective_until' => '2024-01-01',
    ]);

    $response = post(route('activity-times.store'), $data);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['effective_until']);
});

test('show includes schedule period when loaded', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $schedulePeriod = SchedulePeriod::factory()->create(['name' => 'Summer Schedule']);
    $activityTime = ActivityTime::factory()->create([
        'schedule_period_id' => $schedulePeriod->id,
        'effective_from' => '2024-06-01',
        'effective_until' => '2024-08-31',
    ]);

    $response = get(route('activity-times.show', $activityTime));

    $response->assertOk();
    $response->assertJsonPath('data.schedule_period_id', $schedulePeriod->id);
    $response->assertJsonPath('data.schedule_period.id', $schedulePeriod->id);
    $response->assertJsonPath('data.schedule_period.name', 'Summer Schedule');
    $response->assertJsonPath('data.effective_from', '2024-06-01');
    $response->assertJsonPath('data.effective_until', '2024-08-31');
});

test('update can change schedule period', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $schedulePeriod = SchedulePeriod::factory()->create();
    $activityTime = ActivityTime::factory()->create([
        'schedule_period_id' => null,
    ]);
    $data = ActivityTime::factory()->raw([
        'schedule_period_id' => $schedulePeriod->id,
        'effective_from' => '2024-01-01',
        'effective_until' => '2024-03-31',
    ]);

    $response = put(route('activity-times.update', $activityTime), $data);

    $response->assertOk();

    $activityTime->refresh();
    expect($activityTime->schedule_period_id)->toBe($schedulePeriod->id);
    expect($activityTime->effective_from->toDateString())->toBe('2024-01-01');
    expect($activityTime->effective_until->toDateString())->toBe('2024-03-31');
});

test('index can filter by type', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $massActivity = ActivityTime::factory()->create(['type' => 'mass']);
    $confessionActivity = ActivityTime::factory()->create(['type' => 'confession']);

    $response = get(route('activity-times.index', ['type' => 'mass']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $massActivity->id);
});

test('index can filter by church_id', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTime1 = ActivityTime::factory()->create();
    $activityTime2 = ActivityTime::factory()->create();

    $response = get(route('activity-times.index', ['church_id' => $activityTime1->church_id]));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $activityTime1->id);
});

test('index can filter by occurrence', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $weeklyActivity = ActivityTime::factory()->weekly()->create();
    $dailyActivity = ActivityTime::factory()->daily('mass')->create();

    $response = get(route('activity-times.index', ['occurrence' => 'weekly']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.id', $weeklyActivity->id);
});
