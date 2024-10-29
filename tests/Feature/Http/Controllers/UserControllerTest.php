<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;

use function Pest\Faker\fake;
use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertSoftDeleted;

beforeEach(fn () => $this->actingAsRandomUser());

test('index behaves as expected', function (): void {
    $users = User::factory()->count(3)->create();

    $response = get(route('users.index'));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\UserController::class,
        'store',
        \App\Http\Requests\UserStoreRequest::class
    );

test('store saves', function (): void {
    $name = fake()->name();
    $email = fake()->safeEmail();

    $response = post(route('users.store'), [
        'name' => $name,
        'email' => $email,
    ]);

    $users = User::query()
        ->where('name', $name)
        ->where('email', $email)
        ->get();
    expect($users)->toHaveCount(1);
    $user = $users->first();

    $response->assertCreated();
    $response->assertJsonStructure([]);
});

test('show behaves as expected', function (): void {
    $user = User::factory()->create();

    $response = get(route('users.show', $user));

    $response->assertOk();
    $response->assertJsonStructure([]);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\UserController::class,
        'update',
        \App\Http\Requests\UserUpdateRequest::class
    );

test('update behaves as expected', function (): void {
    $user = User::factory()->create();
    $name = fake()->name();
    $email = fake()->safeEmail();

    $response = put(route('users.update', $user), [
        'name' => $name,
        'email' => $email,
    ]);

    $user->refresh();

    $response->assertOk();
    $response->assertJsonStructure([]);

    expect($name)->toEqual($user->name);
    expect($email)->toEqual($user->email);
});

test('destroy deletes and responds with', function (): void {
    $user = User::factory()->create();

    $response = delete(route('users.destroy', $user));

    $response->assertNoContent();

    assertSoftDeleted($user);
});
