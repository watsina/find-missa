<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\SubParish;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use App\Http\Requests\SubParishRequest;
use App\Http\Controllers\SubParishController;

use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->actingAsSuperAdminWithTeam());

test('index behaves as expected', function (): void {
    $subParishes = SubParish::factory()->count(3)->create();

    $response = get(route('sub-parishes.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        SubParishController::class,
        'store',
        SubParishRequest::class
    );

test('store saves', function (): void {
    $data = SubParish::factory()->raw();

    $response = post(route('sub-parishes.store'), $data);

    $response->assertCreated();
    $response->assertJsonStructure([]);

    $subParishes = SubParish::query()
        ->where('name', $data['name'])
        ->get();
    expect($subParishes)->toHaveCount(1);
});

test('show behaves as expected', function (): void {
    $subParish = SubParish::factory()->create();

    $response = get(route('sub-parishes.show', $subParish));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        SubParishController::class,
        'update',
        SubParishRequest::class
    );

test('update behaves as expected', function (): void {
    $subParish = SubParish::factory()->create();
    $data = SubParish::factory()->raw();

    $response = put(route('sub-parishes.update', $subParish), $data);

    $response->assertOk();
    $response->assertJsonStructure([]);

    $subParish->refresh();

    expect($data['name'])->toEqual($subParish->name);
});

test('destroy deletes and responds with', function (): void {
    $subParish = SubParish::factory()->create();

    $response = delete(route('sub-parishes.destroy', $subParish));

    $response->assertNoContent();

    assertModelMissing($subParish);
})->todo('Not supported at the moment.');
