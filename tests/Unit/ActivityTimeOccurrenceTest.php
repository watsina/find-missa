<?php

use App\Models\ActivityTime;
use Illuminate\Support\Carbon;
use App\Enums\ActivityOccurrence;

// Helper function to create ActivityTime instances for unit tests
function makeActivityTime(array $attributes = []): ActivityTime
{
    return new ActivityTime(array_merge([
        'type' => 'mass',
        'activity_type_description' => 'mass',
        'start_time' => '10:00',
        'end_time' => '11:00',
        'language' => 'English',
        'occurrence' => 'weekly',
        'day_of_week' => 'Sunday',
    ], $attributes));
}

// Daily occurrence tests

test('daily activity occurs on any day', function (): void {
    $activity = makeActivityTime([
        'occurrence' => 'daily',
        'day_of_week' => null,
    ]);

    expect($activity->occursOn(Carbon::parse('2024-01-15')))->toBeTrue(); // Monday
    expect($activity->occursOn(Carbon::parse('2024-01-20')))->toBeTrue(); // Saturday
    expect($activity->occursOn(Carbon::parse('2024-01-21')))->toBeTrue(); // Sunday
});

// One-time occurrence tests

test('one-time activity occurs only on specific date', function (): void {
    $activity = makeActivityTime([
        'occurrence' => 'one-time',
        'date' => '2024-03-15',
        'day_of_week' => null,
    ]);

    expect($activity->occursOn(Carbon::parse('2024-03-15')))->toBeTrue();
    expect($activity->occursOn(Carbon::parse('2024-03-14')))->toBeFalse();
    expect($activity->occursOn(Carbon::parse('2024-03-16')))->toBeFalse();
});

// Weekly occurrence tests

test('weekly activity occurs on correct day of week', function (): void {
    $activity = makeActivityTime([
        'occurrence' => 'weekly',
        'day_of_week' => 'Friday',
    ]);

    expect($activity->occursOn(Carbon::parse('2024-01-19')))->toBeTrue(); // Friday
    expect($activity->occursOn(Carbon::parse('2024-01-26')))->toBeTrue(); // Also Friday
    expect($activity->occursOn(Carbon::parse('2024-01-18')))->toBeFalse(); // Thursday
    expect($activity->occursOn(Carbon::parse('2024-01-20')))->toBeFalse(); // Saturday
});

// Monthly nth weekday occurrence tests

test('monthly-nth-weekday activity occurs on first weekday of month', function (): void {
    // First Friday of month
    $activity = makeActivityTime([
        'occurrence' => 'monthly-nth-weekday',
        'day_of_week' => 'Friday',
        'week_of_month' => ActivityTime::WEEK_FIRST,
    ]);

    // First Fridays
    expect($activity->occursOn(Carbon::parse('2024-01-05')))->toBeTrue();
    expect($activity->occursOn(Carbon::parse('2024-02-02')))->toBeTrue();
    expect($activity->occursOn(Carbon::parse('2024-03-01')))->toBeTrue();

    // Second Fridays - should not match
    expect($activity->occursOn(Carbon::parse('2024-01-12')))->toBeFalse();
    expect($activity->occursOn(Carbon::parse('2024-02-09')))->toBeFalse();
});

test('monthly-nth-weekday activity occurs on second weekday of month', function (): void {
    // Second Tuesday of month
    $activity = makeActivityTime([
        'occurrence' => 'monthly-nth-weekday',
        'day_of_week' => 'Tuesday',
        'week_of_month' => ActivityTime::WEEK_SECOND,
    ]);

    // Second Tuesdays
    expect($activity->occursOn(Carbon::parse('2024-01-09')))->toBeTrue();
    expect($activity->occursOn(Carbon::parse('2024-02-13')))->toBeTrue();

    // First Tuesdays - should not match
    expect($activity->occursOn(Carbon::parse('2024-01-02')))->toBeFalse();
    expect($activity->occursOn(Carbon::parse('2024-02-06')))->toBeFalse();
});

test('monthly-nth-weekday activity occurs on third weekday of month', function (): void {
    // Third Monday of month
    $activity = makeActivityTime([
        'occurrence' => 'monthly-nth-weekday',
        'day_of_week' => 'Monday',
        'week_of_month' => ActivityTime::WEEK_THIRD,
    ]);

    // Third Mondays
    expect($activity->occursOn(Carbon::parse('2024-01-15')))->toBeTrue();
    expect($activity->occursOn(Carbon::parse('2024-02-19')))->toBeTrue();
});

test('monthly-nth-weekday activity occurs on fourth weekday of month', function (): void {
    // Fourth Sunday of month
    $activity = makeActivityTime([
        'occurrence' => 'monthly-nth-weekday',
        'day_of_week' => 'Sunday',
        'week_of_month' => ActivityTime::WEEK_FOURTH,
    ]);

    // Fourth Sundays
    expect($activity->occursOn(Carbon::parse('2024-01-28')))->toBeTrue();
    expect($activity->occursOn(Carbon::parse('2024-02-25')))->toBeTrue();
});

test('monthly-nth-weekday activity occurs on last weekday of month', function (): void {
    // Last Friday of month
    $activity = makeActivityTime([
        'occurrence' => 'monthly-nth-weekday',
        'day_of_week' => 'Friday',
        'week_of_month' => ActivityTime::WEEK_LAST,
    ]);

    // Last Fridays
    expect($activity->occursOn(Carbon::parse('2024-01-26')))->toBeTrue(); // Last Friday in Jan 2024
    expect($activity->occursOn(Carbon::parse('2024-02-23')))->toBeTrue(); // Last Friday in Feb 2024
    expect($activity->occursOn(Carbon::parse('2024-03-29')))->toBeTrue(); // Last Friday in Mar 2024

    // Not-last Fridays - should not match
    expect($activity->occursOn(Carbon::parse('2024-01-19')))->toBeFalse();
    expect($activity->occursOn(Carbon::parse('2024-02-16')))->toBeFalse();
});

test('monthly-nth-weekday does not occur on wrong day of week', function (): void {
    // First Friday of month
    $activity = makeActivityTime([
        'occurrence' => 'monthly-nth-weekday',
        'day_of_week' => 'Friday',
        'week_of_month' => ActivityTime::WEEK_FIRST,
    ]);

    // First Monday of Jan 2024 - wrong day
    expect($activity->occursOn(Carbon::parse('2024-01-01')))->toBeFalse();
});

test('monthly-nth-weekday returns false when missing week_of_month', function (): void {
    $activity = makeActivityTime([
        'occurrence' => 'monthly-nth-weekday',
        'day_of_week' => 'Friday',
        'week_of_month' => null,
    ]);

    expect($activity->occursOn(Carbon::parse('2024-01-05')))->toBeFalse();
});

// Week of month label tests

test('week_of_month_label returns correct label', function (): void {
    $first = makeActivityTime(['week_of_month' => ActivityTime::WEEK_FIRST]);
    $second = makeActivityTime(['week_of_month' => ActivityTime::WEEK_SECOND]);
    $third = makeActivityTime(['week_of_month' => ActivityTime::WEEK_THIRD]);
    $fourth = makeActivityTime(['week_of_month' => ActivityTime::WEEK_FOURTH]);
    $last = makeActivityTime(['week_of_month' => ActivityTime::WEEK_LAST]);

    expect($first->week_of_month_label)->toBe('First');
    expect($second->week_of_month_label)->toBe('Second');
    expect($third->week_of_month_label)->toBe('Third');
    expect($fourth->week_of_month_label)->toBe('Fourth');
    expect($last->week_of_month_label)->toBe('Last');
});

test('week_of_month_label returns null when not set', function (): void {
    $activity = makeActivityTime(['week_of_month' => null]);

    expect($activity->week_of_month_label)->toBeNull();
});

// ActivityOccurrence enum tests

test('ActivityOccurrence enum has correct values', function (): void {
    $values = ActivityOccurrence::values();

    expect($values)->toContain('weekly');
    expect($values)->toContain('daily');
    expect($values)->toContain('one-time');
    expect($values)->toContain('monthly-nth-weekday');
});

test('ActivityOccurrence enum labels are correct', function (): void {
    expect(ActivityOccurrence::Weekly->label())->toBe('Weekly');
    expect(ActivityOccurrence::Daily->label())->toBe('Daily');
    expect(ActivityOccurrence::OneTime->label())->toBe('One Time');
    expect(ActivityOccurrence::MonthlyNthWeekday->label())->toBe('Monthly (Specific Week)');
});

test('ActivityOccurrence requiresDayOfWeek is correct', function (): void {
    expect(ActivityOccurrence::Weekly->requiresDayOfWeek())->toBeTrue();
    expect(ActivityOccurrence::MonthlyNthWeekday->requiresDayOfWeek())->toBeTrue();
    expect(ActivityOccurrence::Daily->requiresDayOfWeek())->toBeFalse();
    expect(ActivityOccurrence::OneTime->requiresDayOfWeek())->toBeFalse();
});

test('ActivityOccurrence requiresWeekOfMonth is correct', function (): void {
    expect(ActivityOccurrence::MonthlyNthWeekday->requiresWeekOfMonth())->toBeTrue();
    expect(ActivityOccurrence::Weekly->requiresWeekOfMonth())->toBeFalse();
});
