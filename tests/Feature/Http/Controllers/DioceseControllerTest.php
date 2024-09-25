<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Diocese;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->signIn());

test('index behaves as expected', function (): void {
    $dioceses = Diocese::factory()->count(3)->create();

    $response = get(route('diocese.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\DioceseController::class,
        'store',
        \App\Http\Requests\DioceseStoreRequest::class
    );

test('store saves', function (): void {
    $name = fake()->name();
    $type = fake()->word();
    $country = fake()->country();

    $response = post(route('diocese.store'), [
        'name' => $name,
        'type' => $type,
        'country' => $country,
    ]);

    $dioceses = Diocese::query()
        ->where('name', $name)
        ->where('type', $type)
        ->where('country', $country)
        ->get();
    expect($dioceses)->toHaveCount(1);
    $diocese = $dioceses->first();

    $response->assertCreated();
    $response->assertJsonStructure([]);
});

test('show behaves as expected', function (): void {
    $diocese = Diocese::factory()->create();

    $response = get(route('diocese.show', $diocese));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\DioceseController::class,
        'update',
        \App\Http\Requests\DioceseUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $diocese = Diocese::factory()->create();
    $name = fake()->name();
    $type = fake()->word();
    $country = fake()->country();

    $response = put(route('diocese.update', $diocese), [
        'name' => $name,
        'type' => $type,
        'country' => $country,
    ]);

    $diocese->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);

    expect($name)->toEqual($diocese->name);
    expect($type)->toEqual($diocese->type);
    expect($country)->toEqual($diocese->country);
});

test('destroy deletes and responds with', function (): void {
    $diocese = Diocese::factory()->create();

    $response = delete(route('diocese.destroy', $diocese));

    $response->assertNoContent();

    assertModelMissing($diocese);
});
