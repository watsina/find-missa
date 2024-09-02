<?php

namespace Tests\Feature\Http\Controllers;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;

use App\Models\ActivityException;

use function Pest\Laravel\assertDatabaseHas;

test('index behaves as expected', function (): void {
    $activityExceptions = ActivityException::factory()->count(3)->create();

    $response = get(route('activity-exception.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityExceptionController::class,
        'store',
        \App\Http\Requests\ActivityExceptionStoreRequest::class
    );

test('store saves', function (): void {
    $response = post(route('activity-exception.store'));

    $response->assertCreated();
    $response->assertJsonStructure([]);

    assertDatabaseHas(activityExceptions, [/* ... */]);
});

test('show behaves as expected', function (): void {
    $activityException = ActivityException::factory()->create();

    $response = get(route('activity-exception.show', $activityException));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityExceptionController::class,
        'update',
        \App\Http\Requests\ActivityExceptionUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $activityException = ActivityException::factory()->create();

    $response = put(route('activity-exception.update', $activityException));

    $activityException->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);
});
