<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ActivityTime;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use App\Models\ActivityTimeException;

use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->signIn());

test('index behaves as expected', function (): void {
    $activityTimeExceptions = ActivityTimeException::factory()->count(3)->create();

    $response = get(route('activity-time-exception.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTimeExceptionController::class,
        'store',
        \App\Http\Requests\ActivityTimeExceptionStoreRequest::class
    );

test('store saves', function (): void {
    $activity_time = ActivityTime::factory()->create();
    $date_of_exception = fake()->date;

    $response = post(route('activity-time-exception.store'), [
        'activity_time_id' => $activity_time->id,
        'date_of_exception' => $date_of_exception,
    ]);
    $response->assertCreated();
    $response->assertJsonStructure([]);

    $activityTimeExceptions = ActivityTimeException::query()
        ->where('activity_time_id', $activity_time->id)
        ->get();

    expect($activityTimeExceptions)->toHaveCount(1);
});

test('show behaves as expected', function (): void {
    $activityTimeException = ActivityTimeException::factory()->create();

    $response = get(route('activity-time-exception.show', $activityTimeException));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTimeExceptionController::class,
        'update',
        \App\Http\Requests\ActivityTimeExceptionUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $activityTimeException = ActivityTimeException::factory()->create();
    $activity_time = ActivityTime::factory()->create();
    $date_of_exception = fake()->date;

    $response = put(route('activity-time-exception.update', $activityTimeException), [
        'activity_time_id' => $activity_time->id,
        'date_of_exception' => $date_of_exception,
    ]);

    $response->assertOk();
    $response->assertJsonStructure([]);

    $activityTimeException->refresh();

    expect($activity_time->id)->toEqual($activityTimeException->activity_time_id);
    expect($date_of_exception)->toEqual($activityTimeException->date_of_exception->format('Y-m-d'));
});

test('destroy deletes and responds with', function (): void {
    $activityTimeException = ActivityTimeException::factory()->create();

    $response = delete(route('activity-time-exception.destroy', $activityTimeException));

    $response->assertNoContent();

    assertModelMissing($activityTimeException);
});
