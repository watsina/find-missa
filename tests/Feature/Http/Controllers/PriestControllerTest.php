<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Priest;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;

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
    $parish_type = fake()->word();

    $response = post(route('priest.store'), [
        'name' => $name,
        'parish_type' => $parish_type,
    ]);

    $priests = Priest::query()
        ->where('name', $name)
        ->where('parish_type', $parish_type)
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
    $parish_type = fake()->word();

    $response = put(route('priest.update', $priest), [
        'name' => $name,
        'parish_type' => $parish_type,
    ]);

    $priest->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);

    expect($name)->toEqual($priest->name);
    expect($parish_type)->toEqual($priest->parish_type);
});
