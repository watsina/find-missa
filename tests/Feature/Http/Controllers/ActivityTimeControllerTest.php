<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ActivityTime;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

test('index behaves as expected', function (): void {
    $activityTimes = ActivityTime::factory()->count(3)->create();

    $response = get(route('activity-time.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTimeController::class,
        'store',
        \App\Http\Requests\ActivityTimeStoreRequest::class
    );

test('store saves', function (): void {
    $type = fake()->word();
    $start_time = fake()->word();
    $end_time = fake()->word();
    $language = fake()->word();
    $occurence = fake()->word();

    $response = post(route('activity-time.store'), [
        'type' => $type,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'language' => $language,
        'occurence' => $occurence,
    ]);

    $activityTimes = ActivityTime::query()
        ->where('type', $type)
        ->where('start_time', $start_time)
        ->where('end_time', $end_time)
        ->where('language', $language)
        ->where('occurence', $occurence)
        ->get();
    expect($activityTimes)->toHaveCount(1);
    $activityTime = $activityTimes->first();

    $response->assertCreated();
    $response->assertJsonStructure([]);
});

test('show behaves as expected', function (): void {
    $activityTime = ActivityTime::factory()->create();

    $response = get(route('activity-time.show', $activityTime));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTimeController::class,
        'update',
        \App\Http\Requests\ActivityTimeUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $activityTime = ActivityTime::factory()->create();
    $type = fake()->word();
    $start_time = fake()->word();
    $end_time = fake()->word();
    $language = fake()->word();
    $occurence = fake()->word();

    $response = put(route('activity-time.update', $activityTime), [
        'type' => $type,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'language' => $language,
        'occurence' => $occurence,
    ]);

    $activityTime->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);

    expect($type)->toEqual($activityTime->type);
    expect($start_time)->toEqual($activityTime->start_time);
    expect($end_time)->toEqual($activityTime->end_time);
    expect($language)->toEqual($activityTime->language);
    expect($occurence)->toEqual($activityTime->occurence);
});

test('destroy deletes and responds with', function (): void {
    $activityTime = ActivityTime::factory()->create();

    $response = delete(route('activity-time.destroy', $activityTime));

    $response->assertNoContent();

    assertModelMissing($activityTime);
});
