<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Church;
use App\Models\Parish;
use App\Enums\ChurchType;
use App\Models\SubParish;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->actingAsRandomUser());

test('index behaves as expected', function (): void {
    Church::factory()->count(3)->create();

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
    $church = Church::factory()->raw();

    post(route('church.store'), $church)->assertCreated();

    $churches = Church::query()
        ->where('name', $church['name'])
        ->where('type', $church['type'])
        ->get();
    expect($churches)->toHaveCount(1);
    $church = $churches->first();
});

test('store saves the parishable relation.', function (): void {
    $church = Church::factory()->raw();

    post(route('church.store'), $church)->assertCreated();

    $church = Church::first();

    expect($church->parishable)->not->toBeNull();
});

test('parish is properly validated when creating a church.', function (): void {
    $name = fake()->name();
    $type = fake()->randomElement(['parish', 'basilica', 'minor-basilica']);

    post(route('church.store'), [
        'name' => $name,
        'type' => $type,
        'parishable_id' => SubParish::factory()->createOne(['parish_id' => null])->id,
    ])->assertInvalid('parishable_id');

    $churches = Church::query()
        ->where('name', $name)
        ->where('type', $type)
        ->get();
    expect($churches)->toHaveCount(0);

    post(route('church.store'), [
        'name' => $name,
        'type' => $type,
        'parishable_id' => Parish::factory()->createOne()->id,
    ])->assertCreated();

    $churches = Church::query()
        ->where('name', $name)
        ->where('type', $type)
        ->get();
    expect($churches)->toHaveCount(1);
});

test('sub-parish is properly validated when creating a church.', function (): void {
    $name = fake()->name();
    $type = 'sub-parish';

    post(route('church.store'), [
        'name' => $name,
        'type' => $type,
        'parishable_id' => Parish::factory()->createOne()->id,
    ])->assertInvalid('parishable_id');

    $churches = Church::query()
        ->where('name', $name)
        ->where('type', $type)
        ->get();
    expect($churches)->toHaveCount(0);

    post(route('church.store'), [
        'name' => $name,
        'type' => $type,
        'parishable_id' => SubParish::factory()->createOne(['parish_id' => null])->id,
    ])->assertCreated();

    $churches = Church::query()
        ->where('name', $name)
        ->where('type', $type)
        ->get();
    expect($churches)->toHaveCount(1);
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
    $type = fake()->randomElement(ChurchType::values());

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

test('update also updates the parishable relation.', function (): void {
    $church = Church::factory()->create();
    $newParish = SubParish::factory()->createOne();

    put(route('church.update', $church), [
        ...$church->getAttributes(),
        'type' => 'sub-parish',
        'parishable_id' => $newParish->id,
    ])->assertOk();

    $church = Church::first();

    expect($church->parishable_id)->toBe($newParish->id);
    expect($church->parishable_type)->toBe(SubParish::class);
});

test('destroy deletes and responds with', function (): void {
    $church = Church::factory()->create();

    $response = delete(route('church.destroy', $church));

    $response->assertNoContent();

    assertModelMissing($church);
});
