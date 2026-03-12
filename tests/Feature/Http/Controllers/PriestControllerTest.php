<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Priest;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use App\Http\Requests\PriestStoreRequest;
use App\Http\Controllers\PriestController;
use App\Http\Requests\PriestUpdateRequest;

use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->actingAsSuperAdminWithTeam());

test('index behaves as expected', function (): void {
    $priests = Priest::factory()->count(3)->create();

    $response = get(route('priests.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        PriestController::class,
        'store',
        PriestStoreRequest::class
    );

test('store saves', function (): void {
    $name = fake()->name();

    $response = post(route('priests.store'), [
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

    $response = get(route('priests.show', $priest));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        PriestController::class,
        'update',
        PriestUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $priest = Priest::factory()->create();
    $name = fake()->name();

    $response = put(route('priests.update', $priest), [
        'name' => $name,
    ]);

    $priest->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);

    expect($name)->toEqual($priest->name);
});

test('destroy deletes and responds with', function (): void {
    $priest = Priest::factory()->create();

    $response = delete(route('priests.destroy', $priest));

    $response->assertNoContent();

    assertModelMissing($priest);
})->todo('not supported at the moment');
