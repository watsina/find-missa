<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\ActivityTag;

use function Pest\Laravel\get;
use function Pest\Laravel\put;
use function Pest\Laravel\post;
use function Pest\Laravel\delete;

use App\Enums\ActivityTagCategory;
use Database\Seeders\ActivityTagSeeder;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

beforeEach(function () {
    $this->seed(ActivityTagSeeder::class);
});

test('index returns all available tags for user team', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = get(route('activity-tags.index'));

    $response->assertOk();
    $response->assertJsonCount(10, 'data');
});

test('index can filter by category', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = get(route('activity-tags.index', ['category' => 'time_of_day']));

    $response->assertOk();
    $response->assertJsonCount(6, 'data');
});

test('index can filter by is_system', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Create a custom tag
    ActivityTag::factory()->create([
        'is_system' => false,
        'category' => ActivityTagCategory::Custom->value,
    ]);

    $response = get(route('activity-tags.index', ['is_system' => 'false']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('index can filter by is_user_visible', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = get(route('activity-tags.index', ['is_user_visible' => 'true']));

    $response->assertOk();
    // Only time_of_day tags are user visible (6)
    $response->assertJsonCount(6, 'data');
});

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTagController::class,
        'store',
        \App\Http\Requests\ActivityTag\StoreActivityTagRequest::class
    );

test('store creates a custom tag', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('activity-tags.store'), [
        'name' => 'Students',
        'slug' => 'students',
        'category' => ActivityTagCategory::Audience->value,
        'description' => 'For student activities',
        'color' => '#3B82F6',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Students');
    $response->assertJsonPath('data.slug', 'students');
    $response->assertJsonPath('data.is_system', false);
    $response->assertJsonPath('data.is_user_visible', true);

    $tag = ActivityTag::where('slug', 'students')->first();
    expect($tag)->not->toBeNull();
});

test('store auto-generates slug from name if not provided', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('activity-tags.store'), [
        'name' => 'Working Professionals',
        'category' => ActivityTagCategory::Audience->value,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.slug', 'working-professionals');
});

test('store validates required fields', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('activity-tags.store'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name', 'category']);
});

test('store validates unique slug', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('activity-tags.store'), [
        'name' => 'Morning',
        'slug' => 'morning', // Already exists as a system tag
        'category' => ActivityTagCategory::Custom->value,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['slug']);
});

test('store validates color format', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('activity-tags.store'), [
        'name' => 'Test Tag',
        'slug' => 'test-tag',
        'category' => ActivityTagCategory::Custom->value,
        'color' => 'invalid-color',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['color']);
});

test('show returns a tag', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $tag = ActivityTag::factory()->create();

    $response = get(route('activity-tags.show', $tag));

    $response->assertOk();
    $response->assertJsonPath('data.id', $tag->id);
    $response->assertJsonPath('data.name', $tag->name);
});

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\ActivityTagController::class,
        'update',
        \App\Http\Requests\ActivityTag\UpdateActivityTagRequest::class
    );

test('update modifies a custom tag', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $tag = ActivityTag::factory()->create([
        'is_system' => false,
        'name' => 'Original Name',
    ]);

    $response = put(route('activity-tags.update', $tag), [
        'name' => 'Updated Name',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Updated Name');

    $tag->refresh();
    expect($tag->name)->toBe('Updated Name');
});

test('update fails for system tags', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $systemTag = ActivityTag::where('is_system', true)->first();

    $response = put(route('activity-tags.update', $systemTag), [
        'name' => 'Hacked Name',
    ]);

    $response->assertForbidden();
});

test('admin can delete a custom tag', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $tag = ActivityTag::factory()->create(['is_system' => false]);

    $response = delete(route('activity-tags.destroy', $tag));

    $response->assertNoContent();
    assertModelMissing($tag);
});

test('delete fails for system tags', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $systemTag = ActivityTag::where('is_system', true)->first();

    $response = delete(route('activity-tags.destroy', $systemTag));

    $response->assertForbidden();
    assertModelExists($systemTag);
});

test('non admin cannot delete a tag', function (): void {
    $this->actingAsUserWithTeam();

    $tag = ActivityTag::factory()->create(['is_system' => false]);

    $response = delete(route('activity-tags.destroy', $tag));

    $response->assertForbidden();
    assertModelExists($tag);
});

test('unauthenticated user cannot access tags', function (): void {
    $response = get(route('activity-tags.index'));

    // App redirects to login for unauthenticated users
    $response->assertRedirect();
});
