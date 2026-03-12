<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Team;
use App\Models\SchedulePeriod;

use function Pest\Laravel\get;
use function Pest\Laravel\put;

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

test('index returns all available schedule periods for user team', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Create universal schedule periods
    SchedulePeriod::factory()->count(2)->universal()->create();

    // Create team-specific schedule period for current team
    $user = auth()->user();
    SchedulePeriod::factory()->create(['team_id' => $user->currentTeam->id]);

    $response = get(route('schedule-periods.index'));

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

test('index filters by active status', function (): void {
    $this->actingAsSuperAdminWithTeam();

    SchedulePeriod::factory()->universal()->create(['is_active' => true]);
    SchedulePeriod::factory()->universal()->inactive()->create();

    $response = get(route('schedule-periods.index', ['is_active' => 'true']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.is_active', true);
});

test('index filters by current (in effect now)', function (): void {
    $this->actingAsSuperAdminWithTeam();

    SchedulePeriod::factory()->universal()->current()->create();
    SchedulePeriod::factory()->universal()->upcoming()->create();
    SchedulePeriod::factory()->universal()->past()->create();

    $response = get(route('schedule-periods.index', ['current' => 'true']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('index filters by upcoming', function (): void {
    $this->actingAsSuperAdminWithTeam();

    SchedulePeriod::factory()->universal()->current()->create();
    SchedulePeriod::factory()->universal()->upcoming()->create();
    SchedulePeriod::factory()->universal()->upcoming()->create();

    $response = get(route('schedule-periods.index', ['upcoming' => 'true']));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('index filters by recurring', function (): void {
    $this->actingAsSuperAdminWithTeam();

    SchedulePeriod::factory()->universal()->recurring()->create();
    SchedulePeriod::factory()->universal()->create(['is_recurring' => false]);

    $response = get(route('schedule-periods.index', ['is_recurring' => 'true']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.is_recurring', true);
});

test('index filters by overlapping date range', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Create a period from Jan 1 to Jan 31
    SchedulePeriod::factory()->universal()->dateRange('2025-01-01', '2025-01-31')->create();

    // Create a period from Mar 1 to Mar 31 (no overlap)
    SchedulePeriod::factory()->universal()->dateRange('2025-03-01', '2025-03-31')->create();

    $response = get(route('schedule-periods.index', [
        'overlaps_start' => '2025-01-15',
        'overlaps_end' => '2025-02-15',
    ]));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

test('index does not show other team schedule periods', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $user = auth()->user();

    // Universal period (should be visible)
    SchedulePeriod::factory()->universal()->create();

    // Current team period (should be visible)
    SchedulePeriod::factory()->create(['team_id' => $user->currentTeam->id]);

    // Other team period (should NOT be visible)
    $otherTeam = Team::factory()->create();
    SchedulePeriod::factory()->create(['team_id' => $otherTeam->id]);

    $response = get(route('schedule-periods.index'));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

// =============================================================================
// STORE TESTS
// =============================================================================

test('store uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\SchedulePeriodController::class,
        'store',
        \App\Http\Requests\StoreSchedulePeriodRequest::class
    );

test('store creates a schedule period', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('schedule-periods.store'), [
        'name' => 'Summer Schedule',
        'slug' => 'summer-schedule',
        'description' => 'Mass times for the summer months',
        'start_date' => '2025-06-01',
        'end_date' => '2025-08-31',
        'is_active' => true,
        'is_recurring' => true,
        'is_universal' => true,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.name', 'Summer Schedule');
    $response->assertJsonPath('data.slug', 'summer-schedule');
    $response->assertJsonPath('data.is_active', true);
    $response->assertJsonPath('data.is_recurring', true);
    $response->assertJsonPath('data.is_universal', true);

    $period = SchedulePeriod::where('slug', 'summer-schedule')->first();
    expect($period)->not->toBeNull();
    expect($period->team_id)->toBeNull(); // Universal
});

test('store assigns team_id for team-specific schedule periods', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $user = auth()->user();

    $response = post(route('schedule-periods.store'), [
        'name' => 'Local Summer',
        'slug' => 'local-summer',
        'start_date' => '2025-06-01',
        'end_date' => '2025-08-31',
        'is_universal' => false,
    ]);

    $response->assertCreated();

    $period = SchedulePeriod::where('slug', 'local-summer')->first();
    expect($period->team_id)->toBe($user->currentTeam->id);
});

test('store validates required fields', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('schedule-periods.store'), []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['name', 'slug', 'start_date', 'end_date']);
});

test('store validates unique slug', function (): void {
    $this->actingAsSuperAdminWithTeam();

    SchedulePeriod::factory()->create(['slug' => 'existing-slug']);

    $response = post(route('schedule-periods.store'), [
        'name' => 'Test Period',
        'slug' => 'existing-slug',
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-31',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['slug']);
});

test('store validates end date is after start date', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = post(route('schedule-periods.store'), [
        'name' => 'Test Period',
        'slug' => 'test-period',
        'start_date' => '2025-12-31',
        'end_date' => '2025-01-01',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['end_date']);
});

// =============================================================================
// SHOW TESTS
// =============================================================================

test('show returns a schedule period', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $period = SchedulePeriod::factory()->universal()->summer()->create();

    $response = get(route('schedule-periods.show', $period));

    $response->assertOk();
    $response->assertJsonPath('data.id', $period->id);
    $response->assertJsonPath('data.name', $period->name);
    $response->assertJsonPath('data.slug', $period->slug);
});

test('show returns 403 for other team schedule period', function (): void {
    $this->actingAsUserWithTeam();

    $otherTeam = Team::factory()->create();
    $period = SchedulePeriod::factory()->create(['team_id' => $otherTeam->id]);

    $response = get(route('schedule-periods.show', $period));

    $response->assertForbidden();
});

// =============================================================================
// UPDATE TESTS
// =============================================================================

test('update uses form request validation')
    ->assertActionUsesFormRequest(
        \App\Http\Controllers\SchedulePeriodController::class,
        'update',
        \App\Http\Requests\UpdateSchedulePeriodRequest::class
    );

test('super admin can update universal schedule period', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $period = SchedulePeriod::factory()->universal()->create([
        'name' => 'Original Name',
    ]);

    $response = put(route('schedule-periods.update', $period), [
        'name' => 'Updated Name',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Updated Name');

    $period->refresh();
    expect($period->name)->toBe('Updated Name');
});

test('update can change dates', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $period = SchedulePeriod::factory()->universal()->create([
        'start_date' => '2025-06-01',
        'end_date' => '2025-08-31',
    ]);

    $response = put(route('schedule-periods.update', $period), [
        'start_date' => '2025-05-15',
        'end_date' => '2025-09-15',
    ]);

    $response->assertOk();

    $period->refresh();
    expect($period->start_date->format('Y-m-d'))->toBe('2025-05-15');
    expect($period->end_date->format('Y-m-d'))->toBe('2025-09-15');
});

test('regular user cannot update universal schedule period', function (): void {
    $this->actingAsUserWithTeam();

    $period = SchedulePeriod::factory()->universal()->create();

    $response = put(route('schedule-periods.update', $period), [
        'name' => 'Hacked Name',
    ]);

    $response->assertForbidden();
});

test('super admin can update team schedule period', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $user = auth()->user();

    $period = SchedulePeriod::factory()->create([
        'name' => 'Team Period',
        'team_id' => $user->currentTeam->id,
    ]);

    $response = put(route('schedule-periods.update', $period), [
        'name' => 'Updated Team Period',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'Updated Team Period');
});

test('update validates end date is after start date', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $period = SchedulePeriod::factory()->universal()->create();

    $response = put(route('schedule-periods.update', $period), [
        'start_date' => '2025-12-31',
        'end_date' => '2025-01-01',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['end_date']);
});

// =============================================================================
// DELETE TESTS
// =============================================================================

test('super admin can delete universal schedule period', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $period = SchedulePeriod::factory()->universal()->create();

    $response = delete(route('schedule-periods.destroy', $period));

    $response->assertNoContent();
    assertModelMissing($period);
});

test('regular user cannot delete universal schedule period', function (): void {
    $this->actingAsUserWithTeam();

    $period = SchedulePeriod::factory()->universal()->create();

    $response = delete(route('schedule-periods.destroy', $period));

    $response->assertForbidden();
    assertModelExists($period);
});

test('super admin can delete team schedule period', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $user = auth()->user();

    $period = SchedulePeriod::factory()->create([
        'team_id' => $user->currentTeam->id,
    ]);

    $response = delete(route('schedule-periods.destroy', $period));

    $response->assertNoContent();
    assertModelMissing($period);
});

// =============================================================================
// CURRENT ENDPOINT TESTS
// =============================================================================

test('current returns currently active schedule periods', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Create periods in different states
    SchedulePeriod::factory()->universal()->current()->create(['name' => 'Current 1']);
    SchedulePeriod::factory()->universal()->current()->create(['name' => 'Current 2']);
    SchedulePeriod::factory()->universal()->upcoming()->create(['name' => 'Upcoming']);
    SchedulePeriod::factory()->universal()->past()->create(['name' => 'Past']);

    $response = get(route('schedule-periods.current'));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('current only returns active periods', function (): void {
    $this->actingAsSuperAdminWithTeam();

    SchedulePeriod::factory()->universal()->current()->create(['is_active' => true]);
    SchedulePeriod::factory()->universal()->current()->inactive()->create();

    $response = get(route('schedule-periods.current'));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

// =============================================================================
// UPCOMING ENDPOINT TESTS
// =============================================================================

test('upcoming returns future schedule periods', function (): void {
    $this->actingAsSuperAdminWithTeam();

    SchedulePeriod::factory()->universal()->upcoming()->create(['name' => 'Upcoming 1']);
    SchedulePeriod::factory()->universal()->upcoming()->create(['name' => 'Upcoming 2']);
    SchedulePeriod::factory()->universal()->current()->create(['name' => 'Current']);
    SchedulePeriod::factory()->universal()->past()->create(['name' => 'Past']);

    $response = get(route('schedule-periods.upcoming'));

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('upcoming only returns active periods', function (): void {
    $this->actingAsSuperAdminWithTeam();

    SchedulePeriod::factory()->universal()->upcoming()->create(['is_active' => true]);
    SchedulePeriod::factory()->universal()->upcoming()->inactive()->create();

    $response = get(route('schedule-periods.upcoming'));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
});

// =============================================================================
// CHECK OVERLAP ENDPOINT TESTS
// =============================================================================

test('check overlap returns overlapping periods', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Period from Jan 15 to Feb 15
    SchedulePeriod::factory()->universal()->dateRange('2025-01-15', '2025-02-15')->create();

    $response = get(route('schedule-periods.check-overlap', [
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-31',
    ]));

    $response->assertOk();
    $response->assertJsonPath('has_overlap', true);
    $response->assertJsonCount(1, 'overlapping_periods');
});

test('check overlap returns no overlap when no periods match', function (): void {
    $this->actingAsSuperAdminWithTeam();

    // Period from Mar 1 to Mar 31
    SchedulePeriod::factory()->universal()->dateRange('2025-03-01', '2025-03-31')->create();

    $response = get(route('schedule-periods.check-overlap', [
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-31',
    ]));

    $response->assertOk();
    $response->assertJsonPath('has_overlap', false);
    $response->assertJsonCount(0, 'overlapping_periods');
});

test('check overlap can exclude a period by id', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $periodToExclude = SchedulePeriod::factory()->universal()
        ->dateRange('2025-01-15', '2025-02-15')
        ->create();

    $response = get(route('schedule-periods.check-overlap', [
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-31',
        'exclude_id' => $periodToExclude->id,
    ]));

    $response->assertOk();
    $response->assertJsonPath('has_overlap', false);
});

test('check overlap validates required dates', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = get(route('schedule-periods.check-overlap'));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['start_date', 'end_date']);
});

test('check overlap validates end date is after start date', function (): void {
    $this->actingAsSuperAdminWithTeam();

    $response = get(route('schedule-periods.check-overlap', [
        'start_date' => '2025-12-31',
        'end_date' => '2025-01-01',
    ]));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['end_date']);
});

// =============================================================================
// AUTHENTICATION TESTS
// =============================================================================

test('unauthenticated user cannot access schedule periods', function (): void {
    $response = get(route('schedule-periods.index'));

    $response->assertRedirect();
});

test('unauthenticated user cannot access current endpoint', function (): void {
    $response = get(route('schedule-periods.current'));

    $response->assertRedirect();
});

test('unauthenticated user cannot access upcoming endpoint', function (): void {
    $response = get(route('schedule-periods.upcoming'));

    $response->assertRedirect();
});

test('unauthenticated user cannot access check overlap endpoint', function (): void {
    $response = get(route('schedule-periods.check-overlap', [
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-31',
    ]));

    $response->assertRedirect();
});
