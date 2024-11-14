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

    get(route('dioceses.index'))->assertOk()->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\DioceseController::class,
        'store',
        \App\Http\Requests\DioceseRequest::class
    );

test('store saves', function (): void {
    $this->actingAsUserWithTeam();

    $data = [
        'name' => fake()->name(),
        'type' => fake()->randomElement(['diocese', 'archdiocese']),
    ];
    post(route('dioceses.store'), $data)
        ->assertCreated()
        ->assertJsonStructure([]);

    assertDatabaseHas('dioceses', $data);
});

test('show behaves as expected', function (): void {
    $this->actingAsUserWithTeam();

    $diocese = Diocese::factory()->create();

    $response = get(route('dioceses.show', $diocese));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\DioceseController::class,
        'update',
        \App\Http\Requests\DioceseRequest::class
    );

test('update behaves as expected', function (): void {
    $this->actingAsUserWithTeam();

    $diocese = Diocese::factory()->create();
    $data = [
        'name' => fake()->name(),
        'type' => fake()->randomElement(['diocese', 'archdiocese']),
    ];

    put(route('dioceses.update', $diocese), $data)
        ->assertOk()
        ->assertJsonStructure([]);

    $diocese->refresh();

    expect($data['name'])->toEqual($diocese->name);
    expect($data['type'])->toEqual($diocese->type);
});

test('destroy deletes and responds with', function (): void {
    $this->actingAsUserWithTeam();

    $diocese = Diocese::factory()->create();

    $response = delete(route('dioceses.destroy', $diocese));

    $response->assertNoContent();

    assertModelMissing($diocese);
})->todo('not supported at the moment');
