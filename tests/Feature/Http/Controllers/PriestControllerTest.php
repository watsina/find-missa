<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Priest;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->actingAsUserWithTeam());

test('index behaves as expected', function (): void {
    $priests = Priest::factory()->count(3)->create();

    $response = get(route('priest.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\PriestController::class,
        'store',
        \App\Http\Requests\PriestStoreRequest::class
    );

test('store saves', function (): void {
    $name = fake()->name();

    $response = post(route('priest.store'), [
        'name' => $name,
    ]);

    $priests = Priest::query()
        ->where('name', $name)
        ->get();
    expect($priests)->toHaveCount(1);
    $priest = $priests->first();

    $response->assertCreated();
    $response->assertJsonStructure([]);
});

test('show behaves as expected', function (): void {
    $priest = Priest::factory()->create();

    $response = get(route('priest.show', $priest));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\PriestController::class,
        'update',
        \App\Http\Requests\PriestUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $priest = Priest::factory()->create();
    $name = fake()->name();

    $response = put(route('priest.update', $priest), [
        'name' => $name,
    ]);

    $priest->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);

    expect($name)->toEqual($priest->name);
});

test('destroy deletes and responds with', function (): void {
    $priest = Priest::factory()->create();

    $response = delete(route('priest.destroy', $priest));

    $response->assertNoContent();

    assertModelMissing($priest);
})->todo('not supported at the moment');
