<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Diocese;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;

test('index behaves as expected', function (): void {
    $this->actingAsUserWithTeam();
    Diocese::factory()->count(3)->create();

    get(route('diocese.index'))->assertOk()->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\DioceseController::class,
        'store',
        \App\Http\Requests\DioceseStoreRequest::class
    );

test('store saves', function (): void {
    $this->actingAsUserWithTeam();

    $data = [
        'name' => fake()->name(),
        'type' => fake()->randomElement(['diocese', 'archdiocese']),
    ];
    post(route('diocese.store'), $data)
        ->assertCreated()
        ->assertJsonStructure([]);

    assertDatabaseHas('dioceses', $data);
});

test('show behaves as expected', function (): void {
    $this->actingAsUserWithTeam();

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
    $this->actingAsUserWithTeam();

    $diocese = Diocese::factory()->create();
    $data = [
        'name' => fake()->name(),
        'type' => fake()->randomElement(['diocese', 'archdiocese']),
    ];

    put(route('diocese.update', $diocese), $data)
        ->assertOk()
        ->assertJsonStructure([]);

    $diocese->refresh();

    expect($data['name'])->toEqual($diocese->name);
    expect($data['type'])->toEqual($diocese->type);
});

test('destroy deletes and responds with', function (): void {
    $this->actingAsUserWithTeam();

    $diocese = Diocese::factory()->create();

    $response = delete(route('diocese.destroy', $diocese));

    $response->assertNoContent();

    assertModelMissing($diocese);
});
