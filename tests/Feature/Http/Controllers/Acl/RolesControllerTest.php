<?php

namespace Tests\Feature\Http\Controllers\Acl;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\Permission;

beforeEach(function () {
    // Seed permissions from config
    foreach (config('acl.permissions') as $permissions) {
        collect($permissions)->each(function ($permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description'] ?? null]
            );
        });
    }
});

test('admin can get all roles for current team', function () {
    $user = User::factory()->withTeam()->create(['is_super_admin' => true]);
    $team = $user->currentTeam;

    // Create roles for the user's team
    Role::factory()->forTeam($team)->count(3)->create();

    // Create roles for another team (should not be returned)
    $otherTeam = Team::factory()->create();
    Role::factory()->forTeam($otherTeam)->count(2)->create();

    $this->actingAsUser($user)
        ->get('/api/v1/roles')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

test('admin can get a single role with its permissions', function () {
    $user = User::factory()->withTeam()->create(['is_super_admin' => true]);
    $team = $user->currentTeam;

    $role = Role::factory()->forTeam($team)->has(Permission::factory(3))->create();

    $this->actingAsUser($user)
        ->get('/api/v1/roles/'.$role->id)
        ->assertOk()
        ->assertJsonCount(3, 'data.permissions');
});

test('team owner can create a role', function () {
    $user = User::factory()->withTeam()->create();
    $team = $user->currentTeam;

    $this->actingAsUser($user)
        ->postJson('/api/v1/roles', [
            'name' => 'content-editor',
            'description' => 'Can edit content',
        ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Content Editor') // Name is title-cased by resource
        ->assertJsonPath('data.description', 'Can edit content');

    $this->assertDatabaseHas('roles', [
        'name' => 'content-editor',
        'team_id' => $team->id,
    ]);
});

test('team owner can create a role with permissions', function () {
    $user = User::factory()->withTeam()->create();
    $permissionIds = Permission::take(3)->pluck('id')->toArray();

    $this->actingAsUser($user)
        ->postJson('/api/v1/roles', [
            'name' => 'content-editor',
            'description' => 'Can edit content',
            'permission_ids' => $permissionIds,
        ])
        ->assertCreated()
        ->assertJsonCount(3, 'data.permissions');
});

test('user without permission cannot create a role', function () {
    $owner = User::factory()->withTeam()->create();
    $team = $owner->currentTeam;

    // Create a user who is a team member (not owner) without roles.write permission
    $user = User::factory()->create(['current_team_id' => $team->id]);
    $team->members()->attach($user);

    $this->actingAsUser($user)
        ->postJson('/api/v1/roles', [
            'name' => 'new-role',
            'description' => 'A new role',
        ])
        ->assertForbidden();
});

test('role name must be unique within team', function () {
    $user = User::factory()->withTeam()->create();
    $team = $user->currentTeam;

    Role::factory()->forTeam($team)->create(['name' => 'existing-role']);

    $this->actingAsUser($user)
        ->postJson('/api/v1/roles', [
            'name' => 'existing-role',
            'description' => 'Duplicate role',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('same role name can exist in different teams', function () {
    $user1 = User::factory()->withTeam()->create();
    $team1 = $user1->currentTeam;

    $user2 = User::factory()->withTeam()->create();
    $team2 = $user2->currentTeam;

    Role::factory()->forTeam($team1)->create(['name' => 'editor']);

    // Should be able to create role with same name in different team
    $this->actingAsUser($user2)
        ->postJson('/api/v1/roles', [
            'name' => 'editor',
            'description' => 'Editor in team 2',
        ])
        ->assertCreated();

    $this->assertDatabaseHas('roles', [
        'name' => 'editor',
        'team_id' => $team2->id,
    ]);
});

test('team owner can update a role', function () {
    $user = User::factory()->withTeam()->create();
    $team = $user->currentTeam;

    $role = Role::factory()->forTeam($team)->create(['name' => 'old-name']);
    $permissionIds = Permission::take(4)->pluck('id')->toArray();

    $this->actingAsUser($user)
        ->put('/api/v1/roles/'.$role->id, [
            'name' => 'new-name',
            'description' => 'Updated description',
            'permission_ids' => $permissionIds,
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name') // Name is title-cased by resource
        ->assertJsonPath('data.description', 'Updated description')
        ->assertJsonCount(4, 'data.permissions');
});

test('admin can assign permissions to a role', function () {
    $user = User::factory()->withTeam()->create(['is_super_admin' => true]);
    $team = $user->currentTeam;

    $role = Role::factory()->forTeam($team)->create();
    $permissionIds = Permission::factory(4)->create()->pluck('id');

    $this->actingAsUser($user)
        ->put('/api/v1/roles/'.$role->id, [
            'permission_ids' => $permissionIds->toArray(),
        ])
        ->assertOk()
        ->assertJsonCount(4, 'data.permissions');
});

test('admin can remove permissions from a role', function () {
    $user = User::factory()->withTeam()->create(['is_super_admin' => true]);
    $team = $user->currentTeam;

    $role = Role::factory()->forTeam($team)->has(Permission::factory(5))->create();
    $permissionIds = Permission::factory(2)->create()->pluck('id');

    $this->actingAsUser($user)
        ->put('/api/v1/roles/'.$role->id, [
            'permission_ids' => $permissionIds->toArray(),
        ])
        ->assertOk()
        ->assertJsonCount(2, 'data.permissions');
});

test('team owner can delete a role', function () {
    $user = User::factory()->withTeam()->create();
    $team = $user->currentTeam;

    $role = Role::factory()->forTeam($team)->create();

    $this->actingAsUser($user)
        ->delete('/api/v1/roles/'.$role->id)
        ->assertOk()
        ->assertJsonPath('message', 'Role deleted successfully.');

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('admin can delete a role', function () {
    $owner = User::factory()->withTeam()->create();
    $team = $owner->currentTeam;

    $admin = User::factory()->create([
        'is_super_admin' => true,
        'current_team_id' => $team->id,
    ]);
    $team->members()->attach($admin);

    $role = Role::factory()->forTeam($team)->create();

    $this->actingAsUser($admin)
        ->delete('/api/v1/roles/'.$role->id)
        ->assertOk();

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});

test('user without permission cannot delete a role', function () {
    $owner = User::factory()->withTeam()->create();
    $team = $owner->currentTeam;

    // Create a user who is a team member but not owner/admin
    $user = User::factory()->create(['current_team_id' => $team->id]);
    $team->members()->attach($user);

    $roleToDelete = Role::factory()->forTeam($team)->create(['name' => 'to-delete']);

    // User is not owner/admin, so cannot delete
    $this->actingAsUser($user)
        ->delete('/api/v1/roles/'.$roleToDelete->id)
        ->assertForbidden();
});

test('cannot delete role from another team', function () {
    $user = User::factory()->withTeam()->create();
    $otherTeam = Team::factory()->create();

    $role = Role::factory()->forTeam($otherTeam)->create();

    $this->actingAsUser($user)
        ->delete('/api/v1/roles/'.$role->id)
        ->assertForbidden();
});

test('permissions categories endpoint returns grouped permissions', function () {
    $user = User::factory()->withTeam()->create();

    $this->actingAsUser($user)
        ->get('/api/v1/permissions/categories')
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
