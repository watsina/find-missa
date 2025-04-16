<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ActivityTime;

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
