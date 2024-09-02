<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Church;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

test('index behaves as expected', function (): void {
    $churches = Church::factory()->count(3)->create();

    $response = get(route('church.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ChurchController::class,
        'store',
        \App\Http\Requests\ChurchStoreRequest::class
    );

test('store saves', function (): void {
    $name = fake()->name();
    $type = fake()->word();

    $response = post(route('church.store'), [
        'name' => $name,
        'type' => $type,
    ]);

    $churches = Church::query()
        ->where('name', $name)
        ->where('type', $type)
        ->get();
    expect($churches)->toHaveCount(1);
    $church = $churches->first();

    $response->assertCreated();
    $response->assertJsonStructure([]);
});

test('show behaves as expected', function (): void {
    $church = Church::factory()->create();

    $response = get(route('church.show', $church));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ChurchController::class,
        'update',
        \App\Http\Requests\ChurchUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $church = Church::factory()->create();
    $name = fake()->name();
    $type = fake()->word();

    $response = put(route('church.update', $church), [
        'name' => $name,
        'type' => $type,
    ]);

    $church->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);

    expect($name)->toEqual($church->name);
    expect($type)->toEqual($church->type);
});

test('destroy deletes and responds with', function (): void {
    $church = Church::factory()->create();

    $response = delete(route('church.destroy', $church));

    $response->assertNoContent();

    assertModelMissing($church);
});
