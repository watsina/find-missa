<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Team;
use App\Models\Holiday;
use App\Enums\HolidayType;

use function Pest\Laravel\get;
use function Pest\Laravel\put;

use App\Models\HolidayInstance;
use Database\Seeders\AclSeeder;

use function Pest\Laravel\post;
use function Pest\Laravel\delete;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

beforeEach(function () {
    $this->seed(AclSeeder::class);
});

// =============================================================================
// INDEX TESTS
// =============================================================================

test('index returns all available holidays for user team', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Create universal holidays
    Holiday::factory()->count(3)->create(['is_universal' => true, 'team_id' => null]);

    // Create team-specific holiday for current team
    $user = auth()->user();
    Holiday::factory()->create(['is_universal' => false, 'team_id' => $user->currentTeam->id]);

    $response = get(route('holidays.index'));

    $response->assertOk();
    $response->assertJsonCount(4, 'data');
});

test('index filters by type', function (): void {
    $this->actingAsSuperAdminWithTeam();

    Holiday::factory()->fixed()->create();
    Holiday::factory()->easterRelative()->create();
    Holiday::factory()->computed()->create();

    $response = get(route('holidays.index', ['type' => 'fixed']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.type', 'fixed');
});

test('index filters by holy days of obligation', function (): void {
    $this->actingAsSuperAdminWithTeam();

    Holiday::factory()->holyDayOfObligation()->create();
    Holiday::factory()->create(['is_holy_day_of_obligation' => false]);
    Holiday::factory()->create(['is_holy_day_of_obligation' => false]);

    $response = get(route('holidays.index', ['is_holy_day_of_obligation' => 'true']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.is_holy_day_of_obligation', true);
});

test('index filters by universal holidays', function (): void {
    $this->actingAsSuperAdminWithTeam();

    Holiday::factory()->count(2)->create(['is_universal' => true, 'team_id' => null]);
    $user = auth()->user();
    Holiday::factory()->create(['is_universal' => false, 'team_id' => $user->currentTeam->id]);

    $response = get(route('holidays.index', ['is_universal' => 'true']));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('index filters by moveable feasts', function (): void {
    $this->actingAsSuperAdminWithTeam();

    Holiday::factory()->easterRelative()->create(['is_moveable' => true]);
    Holiday::factory()->fixed()->create(['is_moveable' => false]);

    $response = get(route('holidays.index', ['is_moveable' => 'true']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.is_moveable', true);
});

test('index does not show other team holidays', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $user = auth()->user();

    // Universal holiday (should be visible)
    Holiday::factory()->create(['is_universal' => true, 'team_id' => null]);

    // Current team holiday (should be visible)
    Holiday::factory()->create(['is_universal' => false, 'team_id' => $user->currentTeam->id]);

    // Other team holiday (should NOT be visible)
    $otherTeam = Team::factory()->create();
    Holiday::factory()->create(['is_universal' => false, 'team_id' => $otherTeam->id]);

    $response = get(route('holidays.index'));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

// =============================================================================
// STORE TESTS
// =============================================================================

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\HolidayController::class,
        'store',
        \App\Http\Requests\Holiday\StoreHolidayRequest::class
    );

test('store creates a fixed holiday', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('holidays.store'), [
        'name' => 'New Year Day',
        'slug' => 'new-year-day',
        'type' => HolidayType::Fixed->value,
        'fixed_month' => 1,
        'fixed_day' => 1,
        'is_holy_day_of_obligation' => false,
        'is_universal' => true,
        'is_moveable' => false,
        'liturgical_color' => 'white',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'New Year Day');
    $response->assertJsonPath('data.slug', 'new-year-day');
    $response->assertJsonPath('data.type', 'fixed');
    $response->assertJsonPath('data.fixed_month', 1);
    $response->assertJsonPath('data.fixed_day', 1);

    $holiday = Holiday::where('slug', 'new-year-day')->first();
    expect($holiday)->not->toBeNull();
});

test('store creates an easter-relative holiday', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('holidays.store'), [
        'name' => 'Pentecost',
        'slug' => 'pentecost-test',
        'type' => HolidayType::EasterRelative->value,
        'easter_offset_days' => 49,
        'is_holy_day_of_obligation' => true,
        'is_universal' => true,
        'is_moveable' => true,
        'liturgical_color' => 'red',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Pentecost');
    $response->assertJsonPath('data.type', 'easter_relative');
    $response->assertJsonPath('data.easter_offset_days', 49);
});

test('store creates a computed holiday', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('holidays.store'), [
        'name' => 'Thanksgiving',
        'slug' => 'thanksgiving',
        'type' => HolidayType::Computed->value,
        'computation_rule' => ['nth' => 4, 'day' => 'thursday', 'month' => 11],
        'is_holy_day_of_obligation' => false,
        'is_universal' => false,
        'is_moveable' => true,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Thanksgiving');
    $response->assertJsonPath('data.type', 'computed');
    $response->assertJsonPath('data.computation_rule.nth', 4);
    $response->assertJsonPath('data.computation_rule.day', 'thursday');
    $response->assertJsonPath('data.computation_rule.month', 11);
});

test('store assigns team_id for team-specific holidays', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $user = auth()->user();

    $response = post(route('holidays.store'), [
        'name' => 'Local Feast',
        'slug' => 'local-feast',
        'type' => HolidayType::Fixed->value,
        'fixed_month' => 7,
        'fixed_day' => 15,
        'is_universal' => false,
    ]);

    $response->assertCreated();

    $holiday = Holiday::where('slug', 'local-feast')->first();
    expect($holiday->team_id)->toBe($user->currentTeam->id);
    expect($holiday->is_universal)->toBeFalse();
});

test('store validates required fields', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('holidays.store'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name', 'slug', 'type']);
});

test('store validates unique slug', function (): void {
    $this->actingAsSuperAdminWithTeam();

    Holiday::factory()->create(['slug' => 'existing-slug']);

    $response = post(route('holidays.store'), [
        'name' => 'Test Holiday',
        'slug' => 'existing-slug',
        'type' => HolidayType::Fixed->value,
        'fixed_month' => 1,
        'fixed_day' => 1,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['slug']);
});

test('store validates fixed holiday requires month and day', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('holidays.store'), [
        'name' => 'Test Holiday',
        'slug' => 'test-holiday',
        'type' => HolidayType::Fixed->value,
        // Missing fixed_month and fixed_day
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['fixed_month', 'fixed_day']);
});

test('store validates easter relative holiday requires offset', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('holidays.store'), [
        'name' => 'Test Holiday',
        'slug' => 'test-holiday',
        'type' => HolidayType::EasterRelative->value,
        // Missing easter_offset_days
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['easter_offset_days']);
});

test('store validates computed holiday requires computation rule', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('holidays.store'), [
        'name' => 'Test Holiday',
        'slug' => 'test-holiday',
        'type' => HolidayType::Computed->value,
        // Missing computation_rule
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['computation_rule']);
});

// =============================================================================
// SHOW TESTS
// =============================================================================

test('show returns a holiday', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $holiday = Holiday::factory()->christmas()->create();

    $response = get(route('holidays.show', $holiday));

    $response->assertOk();
    $response->assertJsonPath('data.id', $holiday->id);
    $response->assertJsonPath('data.name', $holiday->name);
    $response->assertJsonPath('data.slug', $holiday->slug);
});

// =============================================================================
// UPDATE TESTS
// =============================================================================

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\HolidayController::class,
        'update',
        \App\Http\Requests\Holiday\UpdateHolidayRequest::class
    );

test('super admin can update universal holiday', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $holiday = Holiday::factory()->create([
        'name' => 'Original Name',
        'is_universal' => true,
        'team_id' => null,
    ]);

    $response = put(route('holidays.update', $holiday), [
        'name' => 'Updated Name',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Updated Name');

    $holiday->refresh();
    expect($holiday->name)->toBe('Updated Name');
});

test('update clears cached instances when holiday definition changes', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $holiday = Holiday::factory()->fixed()->create([
        'fixed_month' => 6,
        'fixed_day' => 15,
    ]);

    // Create an instance
    HolidayInstance::factory()->create([
        'holiday_id' => $holiday->id,
        'year' => 2025,
        'date' => '2025-06-15',
    ]);

    expect($holiday->instances()->count())->toBe(1);

    $response = put(route('holidays.update', $holiday), [
        'fixed_day' => 20,
    ]);

    $response->assertOk();

    // Instances should be cleared
    expect($holiday->instances()->count())->toBe(0);
});

test('regular user cannot update universal holiday', function (): void {
    $this->actingAsUserWithTeam();

    $holiday = Holiday::factory()->create([
        'is_universal' => true,
        'team_id' => null,
    ]);

    $response = put(route('holidays.update', $holiday), [
        'name' => 'Hacked Name',
    ]);

    $response->assertForbidden();
});

test('user with permission can update team holiday', function (): void {
    // Super admin has implicit permission to update team holidays
    $this->actingAsSuperAdminWithTeam();

    $user = auth()->user();

    $holiday = Holiday::factory()->create([
        'name' => 'Team Holiday',
        'is_universal' => false,
        'team_id' => $user->currentTeam->id,
    ]);

    $response = put(route('holidays.update', $holiday), [
        'name' => 'Updated Team Holiday',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Updated Team Holiday');
});

// =============================================================================
// DELETE TESTS
// =============================================================================

test('super admin can delete universal holiday', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $holiday = Holiday::factory()->create([
        'is_universal' => true,
        'team_id' => null,
    ]);

    $response = delete(route('holidays.destroy', $holiday));

    $response->assertNoContent();
    assertModelMissing($holiday);
});

test('regular user cannot delete universal holiday', function (): void {
    $this->actingAsUserWithTeam();

    $holiday = Holiday::factory()->create([
        'is_universal' => true,
        'team_id' => null,
    ]);

    $response = delete(route('holidays.destroy', $holiday));

    $response->assertForbidden();
    assertModelExists($holiday);
});

test('user with permission can delete team holiday', function (): void {
    // Super admin has implicit permission to delete team holidays
    $this->actingAsSuperAdminWithTeam();

    $user = auth()->user();

    $holiday = Holiday::factory()->create([
        'is_universal' => false,
        'team_id' => $user->currentTeam->id,
    ]);

    $response = delete(route('holidays.destroy', $holiday));

    $response->assertNoContent();
    assertModelMissing($holiday);
});

test('deleting holiday also deletes its instances', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $holiday = Holiday::factory()->create(['is_universal' => true, 'team_id' => null]);
    $instance = HolidayInstance::factory()->create(['holiday_id' => $holiday->id]);

    $response = delete(route('holidays.destroy', $holiday));

    $response->assertNoContent();
    assertModelMissing($holiday);
    assertModelMissing($instance);
});

// =============================================================================
// INSTANCES ENDPOINT TESTS
// =============================================================================

test('instances returns holiday instances for a year', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Create a fixed holiday
    Holiday::factory()->christmas()->create();
    Holiday::factory()->easter()->create();

    $response = get(route('holidays.instances', ['year' => 2025]));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');

    // Check Christmas date
    $response->assertJsonFragment(['date' => '2025-12-25']);
});

test('instances computes easter-relative holidays correctly', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Easter 2025 is April 20
    Holiday::factory()->ashWednesday()->create();

    $response = get(route('holidays.instances', ['year' => 2025]));

    $response->assertOk();
    // Ash Wednesday is 46 days before Easter (April 20 - 46 days = March 5)
    $response->assertJsonFragment(['date' => '2025-03-05']);
});

// =============================================================================
// IN RANGE ENDPOINT TESTS
// =============================================================================

test('in range returns holidays within date range', function (): void {
    $this->actingAsSuperAdminWithTeam();

    Holiday::factory()->christmas()->create();
    Holiday::factory()->easter()->create();

    $response = get(route('holidays.in-range', [
        'start_date' => '2025-03-01',
        'end_date' => '2025-05-31',
    ]));

    $response->assertOk();
    // Only Easter (April 20) is in this range
    $response->assertJsonCount(1, 'data');
    $response->assertJsonFragment(['date' => '2025-04-20']);
});

test('in range validates required dates', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = get(route('holidays.in-range'));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['start_date', 'end_date']);
});

test('in range validates end date is after start date', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = get(route('holidays.in-range', [
        'start_date' => '2025-12-31',
        'end_date' => '2025-01-01',
    ]));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['end_date']);
});

// =============================================================================
// CHECK DATE ENDPOINT TESTS
// =============================================================================

test('check date returns holiday info for a date', function (): void {
    $this->actingAsSuperAdminWithTeam();

    Holiday::factory()->christmas()->create();

    $response = get(route('holidays.check-date', ['date' => '2025-12-25']));

    $response->assertOk();
    $response->assertJsonPath('is_holiday', true);
    $response->assertJsonPath('is_holy_day_of_obligation', true);
    $response->assertJsonCount(1, 'holidays');
});

test('check date returns false for non-holiday', function (): void {
    $this->actingAsSuperAdminWithTeam();

    Holiday::factory()->christmas()->create();

    $response = get(route('holidays.check-date', ['date' => '2025-07-15']));

    $response->assertOk();
    $response->assertJsonPath('is_holiday', false);
    $response->assertJsonPath('is_holy_day_of_obligation', false);
    $response->assertJsonCount(0, 'holidays');
});

test('check date validates date parameter', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = get(route('holidays.check-date'));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['date']);
});

test('check date correctly identifies holy day of obligation', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Create a holiday that is NOT a holy day of obligation
    Holiday::factory()->fixed()->create([
        'slug' => 'test-non-obligation',
        'fixed_month' => 6,
        'fixed_day' => 15,
        'is_holy_day_of_obligation' => false,
    ]);

    $response = get(route('holidays.check-date', ['date' => '2025-06-15']));

    $response->assertOk();
    $response->assertJsonPath('is_holiday', true);
    $response->assertJsonPath('is_holy_day_of_obligation', false);
});

// =============================================================================
// AUTHENTICATION TESTS
// =============================================================================

test('unauthenticated user cannot access holidays', function (): void {
    $response = get(route('holidays.index'));

    $response->assertRedirect();
});
