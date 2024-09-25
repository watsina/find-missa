<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\SubParish;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->signIn());

test('index behaves as expected', function (): void {
    $subParishes = SubParish::factory()->count(3)->create();

    $response = get(route('sub-parish.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\SubParishController::class,
        'store',
        \App\Http\Requests\SubParishStoreRequest::class
    );

test('store saves', function (): void {
    $name = fake()->name();

    $response = post(route('sub-parish.store'), [
        'name' => $name,
    ]);

    $subParishes = SubParish::query()
        ->where('name', $name)
        ->get();
    expect($subParishes)->toHaveCount(1);
    $subParish = $subParishes->first();

    $response->assertCreated();
    $response->assertJsonStructure([]);
});

test('show behaves as expected', function (): void {
    $subParish = SubParish::factory()->create();

    $response = get(route('sub-parish.show', $subParish));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\SubParishController::class,
        'update',
        \App\Http\Requests\SubParishUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $subParish = SubParish::factory()->create();
    $name = fake()->name();

    $response = put(route('sub-parish.update', $subParish), [
        'name' => $name,
    ]);

    $subParish->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);

    expect($name)->toEqual($subParish->name);
});

test('destroy deletes and responds with', function (): void {
    $subParish = SubParish::factory()->create();

    $response = delete(route('sub-parish.destroy', $subParish));

    $response->assertNoContent();

    assertModelMissing($subParish);
});
