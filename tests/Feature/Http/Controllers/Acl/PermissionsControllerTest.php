<?php

namespace Tests\Feature\Http\Controllers\Acl;

use App\Models\Permission;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\assertDatabaseHas;

test('it returns all the configured permissions', function () {
    Permission::factory(10)->create();

    $this->actingAsSuperAdmin();

    get(route('permissions.index'))
        ->assertOk()
        ->assertJsonCount(10, 'data');
});

test('it returns a permissions details.', function () {
    $permission = Permission::factory()->create(['name' => 'permission']);

    $this->actingAsSuperAdmin();

    get(route('permissions.show', $permission))
        ->assertOk()
        ->assertJsonFragment(['name' => 'permission']);
});

test('it creates a permission.', function () {
    $data = Permission::factory()->raw();

    $this->actingAsSuperAdmin();

    post(route('permissions.store'), $data)
        ->assertCreated();

    assertDatabaseHas('permissions', $data);
});

test('it updates a permission.', function () {
    $this->actingAsSuperAdmin();
    $permission = Permission::factory()->create();

    $data = [
        'name' => 'new_name',
        'description' => 'new description',
    ];

    put(route('permissions.update', $permission), $data)
        ->assertOk();

    assertDatabaseHas('permissions', $data);
});
