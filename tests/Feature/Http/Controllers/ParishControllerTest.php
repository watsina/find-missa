<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Parish;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->actingAsRandomUser());

test('index behaves as expected', function (): void {
    $parishes = Parish::factory()->count(3)->create();

    $response = get(route('parish.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ParishController::class,
        'store',
        \App\Http\Requests\ParishStoreRequest::class
    );

test('store saves', function (): void {
    $name = fake()->name();

    $response = post(route('parish.store'), [
        'name' => $name,
    ]);

    $parishes = Parish::query()
        ->where('name', $name)
        ->get();
    expect($parishes)->toHaveCount(1);
    $parish = $parishes->first();

    $response->assertCreated();
    $response->assertJsonStructure([]);
});

test('show behaves as expected', function (): void {
    $parish = Parish::factory()->create();

    $response = get(route('parish.show', $parish));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ParishController::class,
        'update',
        \App\Http\Requests\ParishUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $parish = Parish::factory()->create();
    $name = fake()->name();

    $response = put(route('parish.update', $parish), [
        'name' => $name,
    ]);

    $parish->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);

    expect($name)->toEqual($parish->name);
});

test('destroy deletes and responds with', function (): void {
    $parish = Parish::factory()->create();

    $response = delete(route('parish.destroy', $parish));

    $response->assertNoContent();

    assertModelMissing($parish);
});
