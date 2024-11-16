<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Parish;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->actingAsSuperAdminWithTeam());

test('index behaves as expected', function (): void {
    $parishes = Parish::factory()->count(3)->create();

    $response = get(route('parishes.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ParishController::class,
        'store',
        \App\Http\Requests\ParishRequest::class
    );

test('store saves', function (): void {
    $data = Parish::factory()->raw();

    $response = post(route('parishes.store'), $data);
    $response->assertCreated();
    $response->assertJsonStructure([]);

    $parishes = Parish::query()
        ->where('name', $data['name'])
        ->get();
    expect($parishes)->toHaveCount(1);
});

test('show behaves as expected', function (): void {
    $parish = Parish::factory()->create();

    get(route('parishes.show', $parish))
        ->assertOk()
        ->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ParishController::class,
        'update',
        \App\Http\Requests\ParishRequest::class
    );

test('update behaves as expected', function (): void {
    $parish = Parish::factory()->create();
    $data = Parish::factory()->raw();

    put(route('parishes.update', $parish), $data)
        ->assertOk()
        ->assertJsonStructure([]);

    $parish->refresh();

    expect($data['name'])->toEqual($parish->name);
});

test('destroy deletes and responds with', function (): void {
    $parish = Parish::factory()->create();

    delete(route('parishes.destroy', $parish))->assertNoContent();

    assertModelMissing($parish);
})->todo('not yet supported');
