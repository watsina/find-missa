<?php

namespace Tests\Feature\Http\Controllers\AppUsers;

use App\Models\Team;
use App\Models\Holiday;

use function Pest\Laravel\get;

// =============================================================================
// PUBLIC HOLIDAY INFO TESTS
// =============================================================================

test('holiday info returns holiday data for a date without authentication', function (): void {
    // Create a universal holiday (Christmas)
    Holiday::factory()->christmas()->create();

    $response = get(route('holiday-info', ['date' => '2025-12-25']));

    $response->assertOk();
    $response->assertJsonPath('is_holiday', true);
    $response->assertJsonPath('is_holy_day_of_obligation', true);
    $response->assertJsonCount(1, 'holidays');
    $response->assertJsonPath('holidays.0.holiday.name', 'Christmas');
});

test('holiday info returns false for non-holiday date', function (): void {
    // Create a holiday
    Holiday::factory()->christmas()->create();

    $response = get(route('holiday-info', ['date' => '2025-07-15']));

    $response->assertOk();
    $response->assertJsonPath('is_holiday', false);
    $response->assertJsonPath('is_holy_day_of_obligation', false);
    $response->assertJsonCount(0, 'holidays');
});

test('holiday info validates date parameter is required', function (): void {
    $response = get(route('holiday-info'));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['date']);
});

test('holiday info validates date parameter format', function (): void {
    $response = get(route('holiday-info', ['date' => 'not-a-date']));

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['date']);
});

test('holiday info only returns universal holidays', function (): void {
    // Create a team for the team-specific holiday
    $team = Team::factory()->create();

    // Create a universal holiday
    Holiday::factory()->fixed()->create([
        'slug' => 'universal-holiday',
        'fixed_month' => 7,
        'fixed_day' => 15,
        'is_universal' => true,
        'team_id' => null,
    ]);

    // Create a team-specific holiday (should NOT be returned)
    Holiday::factory()->fixed()->create([
        'slug' => 'team-holiday',
        'fixed_month' => 7,
        'fixed_day' => 15,
        'is_universal' => false,
        'team_id' => $team->id,
    ]);

    $response = get(route('holiday-info', ['date' => '2025-07-15']));

    $response->assertOk();
    $response->assertJsonPath('is_holiday', true);
    $response->assertJsonCount(1, 'holidays');
    $response->assertJsonPath('holidays.0.holiday.slug', 'universal-holiday');
});

test('holiday info correctly identifies non-holy day of obligation', function (): void {
    // Create a holiday that is NOT a holy day of obligation
    Holiday::factory()->fixed()->create([
        'slug' => 'regular-feast',
        'fixed_month' => 6,
        'fixed_day' => 24,
        'is_holy_day_of_obligation' => false,
    ]);

    $response = get(route('holiday-info', ['date' => '2025-06-24']));

    $response->assertOk();
    $response->assertJsonPath('is_holiday', true);
    $response->assertJsonPath('is_holy_day_of_obligation', false);
});

test('holiday info returns multiple holidays on the same date', function (): void {
    // Create two holidays on the same date
    Holiday::factory()->fixed()->create([
        'slug' => 'feast-one',
        'fixed_month' => 8,
        'fixed_day' => 15,
        'is_universal' => true,
        'team_id' => null,
    ]);

    Holiday::factory()->fixed()->create([
        'slug' => 'feast-two',
        'fixed_month' => 8,
        'fixed_day' => 15,
        'is_universal' => true,
        'team_id' => null,
    ]);

    $response = get(route('holiday-info', ['date' => '2025-08-15']));

    $response->assertOk();
    $response->assertJsonPath('is_holiday', true);
    $response->assertJsonCount(2, 'holidays');
});

test('holiday info handles easter-relative holidays correctly', function (): void {
    // Easter 2025 is April 20
    Holiday::factory()->easter()->create();

    $response = get(route('holiday-info', ['date' => '2025-04-20']));

    $response->assertOk();
    $response->assertJsonPath('is_holiday', true);
    $response->assertJsonCount(1, 'holidays');
});

test('holiday info returns liturgical color in response', function (): void {
    Holiday::factory()->christmas()->create([
        'liturgical_color' => 'white',
    ]);

    $response = get(route('holiday-info', ['date' => '2025-12-25']));

    $response->assertOk();
    $response->assertJsonPath('holidays.0.holiday.liturgical_color', 'white');
});
