<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Church;
use App\Models\Parish;
use App\Models\SubParish;
use App\Models\ActivityTime;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelMissing;

beforeEach(fn () => $this->actingAsUserWithTeam());

test('index behaves as expected', function (): void {
    Church::factory()->count(3)->create();

    $response = get(route('churches.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ChurchController::class,
        'store',
        \App\Http\Requests\ChurchRequest::class
    );

test('store saves', function (): void {
    $church = Church::factory()->raw();

    post(route('churches.store'), $church)->assertCreated();

    $churches = Church::query()
        ->where('name', $church['name'])
        ->where('type', $church['type'])
        ->get();
    expect($churches)->toHaveCount(1);
    $church = $churches->first();
});

test('store saves the parishable relation.', function (): void {
    $church = Church::factory()->raw();

    post(route('churches.store'), $church)->assertCreated();

    $church = Church::first();

    expect($church->parishable)->not->toBeNull();
});

test('parish is properly validated when creating a churches.', function (): void {
    $name = fake()->name();
    $type = fake()->randomElement(['parish', 'basilica', 'minor-basilica']);

    $data = Church::factory()->raw([
        'name' => $name,
        'type' => $type,
        'parishable_id' => SubParish::factory()->createOne(['parish_id' => null])->id,
    ]);

    post(route('churches.store'), $data)->assertInvalid('parishable_id');

    $churches = Church::query()
        ->where('name', $name)
        ->where('type', $type)
        ->get();
    expect($churches)->toHaveCount(0);

    $data['parishable_id'] = Parish::factory()->createOne()->id;

    post(route('churches.store'), $data)->assertCreated();

    $churches = Church::query()
        ->where('name', $name)
        ->where('type', $type)
        ->get();
    expect($churches)->toHaveCount(1);
});

test('sub-parish is properly validated when creating a churches.', function (): void {
    $name = fake()->name();
    $type = 'sub-parish';

    $data = Church::factory()->raw([
        'name' => $name,
        'type' => $type,
        'parishable_id' => Parish::factory()->createOne()->id,
    ]);

    post(route('churches.store'), $data)->assertInvalid('parishable_id');

    $churches = Church::query()
        ->where('name', $name)
        ->where('type', $type)
        ->get();
    expect($churches)->toHaveCount(0);

    $data['parishable_id'] = SubParish::factory()->createOne(['parish_id' => null])->id;

    post(route('churches.store'), $data)->assertCreated();

    $churches = Church::query()
        ->where('name', $name)
        ->where('type', $type)
        ->get();
    expect($churches)->toHaveCount(1);
});

test('it shows the church with its activities', function (): void {
    $church = Church::factory()->has(ActivityTime::factory(3), 'activities')->create();

    get(route('churches.show', $church))
        ->assertOk()
        ->assertJsonCount(3, 'data.activities');
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ChurchController::class,
        'update',
        \App\Http\Requests\ChurchRequest::class
    );

test('update behaves as expected', function (): void {
    $church = Church::factory()->create();
    $name = fake()->name();

    $data = Church::factory()->raw([
        'name' => $name,
    ]);

    put(route('churches.update', $church), $data)
        ->assertOk()
        ->assertJsonStructure([]);

    $church->refresh();

    expect($name)->toEqual($church->name);
});

test('update also updates the parishable relation.', function (): void {
    $church = Church::factory()->create();
    $newParish = SubParish::factory()->createOne();

    put(route('churches.update', $church), [
        ...$church->getAttributes(),
        'type' => 'sub-parish',
        'parishable_id' => $newParish->id,
    ])->assertOk();

    $church = Church::first();

    expect($church->parishable_id)->toBe($newParish->id);
    expect($church->parishable_type)->toBe('subParish');
});

test('destroy deletes and responds with', function (): void {
    $church = Church::factory()->create();

    $response = delete(route('churches.destroy', $church));

    $response->assertNoContent();

    assertModelMissing($church);
})->todo('not supported for now.');
