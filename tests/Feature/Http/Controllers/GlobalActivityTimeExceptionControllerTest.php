<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ActivityTime;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use App\Models\GlobalActivityTimeException;

use function Pest\Laravel\assertModelMissing;

test('index behaves as expected returns the expected number of items', function (): void {
    $this->actingAsUserWithTeam();

    GlobalActivityTimeException::factory()->count(3)->create();

    get(route('global-activity-time-exceptions.index'))
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\GlobalActivityTimeExceptionController::class,
        'store',
        \App\Http\Requests\StoreGlobalActivityTimeExceptionRequest::class
    );

test('store saves', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activity_time = ActivityTime::factory()->create();
    $data = GlobalActivityTimeException::factory()->raw(['activity_time_id' => $activity_time->id]);

    $response = post(route('global-activity-time-exceptions.store'), $data);
    $response->assertCreated();
    $response->assertJsonStructure([]);

    $activityTimeExceptions = GlobalActivityTimeException::query()
        ->where('activity_time_id', $activity_time->id)
        ->get();

    expect($activityTimeExceptions)->toHaveCount(1);
})->skip('to refactor');

test('show behaves as expected', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTimeException = GlobalActivityTimeException::factory()->create();

    $response = get(route('global-activity-time-exceptions.show', $activityTimeException));

    $response->assertOk();
    $response->assertJsonStructure([]);
})->skip('to refactor');

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\GlobalActivityTimeExceptionController::class,
        'update',
        \App\Http\Requests\UpdateGlobalActivityTimeExceptionRequest::class
    );

test('update behaves as expected', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTimeException = GlobalActivityTimeException::factory()->create();
    $activity_time = ActivityTime::factory()->create();
    $data = GlobalActivityTimeException::factory()->raw(['activity_time_id' => $activity_time->id]);

    $response = put(route('global-activity-time-exceptions.update', $activityTimeException), $data);

    $response->assertOk();
    $response->assertJsonStructure([]);

    $activityTimeException->refresh();

    expect($activity_time->id)->toEqual($activityTimeException->activity_time_id);
    expect($data['start_date'])->toEqual($activityTimeException->start_date->format('Y-m-d'));
    expect($data['end_date'])->toEqual($activityTimeException->end_date->format('Y-m-d'));
})->skip('to refactor');

test('destroy deletes and responds with', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $activityTimeException = GlobalActivityTimeException::factory()->create();

    $response = delete(route('global-activity-time-exceptions.destroy', $activityTimeException));

    $response->assertNoContent();

    assertModelMissing($activityTimeException);
})->todo('not suppported at the moment');
