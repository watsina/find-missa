<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ActivityTime;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use App\Models\ActivityTimeException;

use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->actingAsUserWithTeam());

test('index behaves as expected', function (): void {
    $activityTimeExceptions = ActivityTimeException::factory()->count(3)->create();

    $response = get(route('activity-time-exceptions.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTimeExceptionController::class,
        'store',
        \App\Http\Requests\ActivityTimeExceptionRequest::class
    );

test('store saves', function (): void {
    $activity_time = ActivityTime::factory()->create();
    $data = ActivityTimeException::factory()->raw(['activity_time_id' => $activity_time->id]);

    $response = post(route('activity-time-exceptions.store'), $data);
    $response->assertCreated();
    $response->assertJsonStructure([]);

    $activityTimeExceptions = ActivityTimeException::query()
        ->where('activity_time_id', $activity_time->id)
        ->get();

    expect($activityTimeExceptions)->toHaveCount(1);
});

test('show behaves as expected', function (): void {
    $activityTimeException = ActivityTimeException::factory()->create();

    $response = get(route('activity-time-exceptions.show', $activityTimeException));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTimeExceptionController::class,
        'update',
        \App\Http\Requests\ActivityTimeExceptionRequest::class
    );

test('update behaves as expected', function (): void {
    $activityTimeException = ActivityTimeException::factory()->create();
    $activity_time = ActivityTime::factory()->create();
    $data = ActivityTimeException::factory()->raw(['activity_time_id' => $activity_time->id]);

    $response = put(route('activity-time-exceptions.update', $activityTimeException), $data);

    $response->assertOk();
    $response->assertJsonStructure([]);

    $activityTimeException->refresh();

    expect($activity_time->id)->toEqual($activityTimeException->activity_time_id);
    expect($data['start_date'])->toEqual($activityTimeException->start_date->format('Y-m-d'));
    expect($data['end_date'])->toEqual($activityTimeException->end_date->format('Y-m-d'));
});

test('destroy deletes and responds with', function (): void {
    $activityTimeException = ActivityTimeException::factory()->create();

    $response = delete(route('activity-time-exceptions.destroy', $activityTimeException));

    $response->assertNoContent();

    assertModelMissing($activityTimeException);
});
