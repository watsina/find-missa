<?php

namespace Tests\Feature\Http\Controllers\Acl;

use App\Models\Permission;

use function Pest\Laravel\get;

test('it returns all the configured permissions', function () {
    $this->actingAsSuperAdminWithTeam();

    $allPermissions = Permission::all();

    get(route('permissions.index'))
        ->assertOk()
        ->assertJsonCount($allPermissions->count(), 'data');
});

test('each permission has a name and display_name', function () {
    $this->actingAsSuperAdminWithTeam();

    get(route('permissions.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['name', 'description'],
            ],
        ]);
});

test('permission categories endpoint returns grouped permissions', function () {
    $this->actingAsSuperAdminWithTeam();

    get(route('permissions.categories'))
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'key',
                    'label',
                    'permissions',
                ],
            ],
        ]);
});

test('categories match those defined in config', function () {
    $this->actingAsSuperAdminWithTeam();

    $expectedCategories = array_keys(config('acl.permissions'));

    $response = get(route('permissions.categories'))
        ->assertOk();

    $returnedKeys = collect($response->json('data'))->pluck('key')->all();

    expect($returnedKeys)->toBe($expectedCategories);
});

test('unauthenticated user cannot access permissions', function () {
    get(route('permissions.index'))
        ->assertRedirect();
});
