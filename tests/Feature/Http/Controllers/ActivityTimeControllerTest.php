<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ActivityTime;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->actingAsUserWithTeam());

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
        \App\Http\Requests\ActivityTimeRequest::class
    );

test('store saves', function (): void {
    $data = ActivityTime::factory()->raw();

    post(route('activity-time.store'), $data)
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
        \App\Http\Requests\ActivityTimeRequest::class
    );

test('update behaves as expected', function (): void {
    $activityTime = ActivityTime::factory()->create();
    $data = ActivityTime::factory()->raw();

    put(route('activity-time.update', $activityTime), [
        'type' => $data['type'],
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

test('destroy deletes and responds with', function (): void {
    $activityTime = ActivityTime::factory()->create();

    $response = delete(route('activity-time.destroy', $activityTime));

    $response->assertNoContent();

    assertModelMissing($activityTime);
});
