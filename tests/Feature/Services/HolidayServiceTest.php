<?php

use Carbon\Carbon;
use App\Models\Team;
use App\Models\Holiday;
use App\Enums\HolidayType;
use App\Models\HolidayInstance;
use App\Services\HolidayService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(HolidayService::class);
});

/*
|--------------------------------------------------------------------------
| Easter Date Calculation Tests
|--------------------------------------------------------------------------
*/

it('calculates easter date correctly for known years', function (int $year, string $expectedDate) {
    $easter = $this->service->getEasterDate($year);

    expect($easter->toDateString())->toBe($expectedDate);
})->with([
    '2020' => [2020, '2020-04-12'],
    '2021' => [2021, '2021-04-04'],
    '2022' => [2022, '2022-04-17'],
    '2023' => [2023, '2023-04-09'],
    '2024' => [2024, '2024-03-31'],
    '2025' => [2025, '2025-04-20'],
    '2026' => [2026, '2026-04-05'],
    '2030' => [2030, '2030-04-21'],
    '2035' => [2035, '2035-03-25'],
]);

it('caches easter date calculation', function () {
    Cache::flush();

    // First call should calculate
    $easter1 = $this->service->getEasterDate(2025);

    // Verify it's cached
    expect(Cache::has('easter_date_2025'))->toBeTrue();

    // Second call should use cache
    $easter2 = $this->service->getEasterDate(2025);

    expect($easter1->toDateString())->toBe($easter2->toDateString());
});

/*
|--------------------------------------------------------------------------
| Fixed Date Holiday Tests
|--------------------------------------------------------------------------
*/

it('computes fixed date holiday correctly', function () {
    $holiday = Holiday::factory()->fixed(12, 25)->create([
        'name' => 'Christmas Day',
    ]);

    $date = $this->service->computeHolidayDate($holiday, 2025);

    expect($date->toDateString())->toBe('2025-12-25');
});

it('computes fixed date for different years', function () {
    $holiday = Holiday::factory()->fixed(1, 1)->create([
        'name' => 'Solemnity of Mary',
    ]);

    expect($this->service->computeHolidayDate($holiday, 2024)->toDateString())->toBe('2024-01-01');
    expect($this->service->computeHolidayDate($holiday, 2025)->toDateString())->toBe('2025-01-01');
    expect($this->service->computeHolidayDate($holiday, 2026)->toDateString())->toBe('2026-01-01');
});

/*
|--------------------------------------------------------------------------
| Easter Relative Holiday Tests
|--------------------------------------------------------------------------
*/

it('computes easter relative holidays with positive offset', function () {
    $holiday = Holiday::factory()->easterRelative(49)->create([
        'name' => 'Pentecost',
    ]);

    // Easter 2025 is April 20, Pentecost is 49 days later = June 8
    $date = $this->service->computeHolidayDate($holiday, 2025);

    expect($date->toDateString())->toBe('2025-06-08');
});

it('computes easter relative holidays with negative offset', function () {
    $holiday = Holiday::factory()->easterRelative(-46)->create([
        'name' => 'Ash Wednesday',
    ]);

    // Easter 2025 is April 20, Ash Wednesday is 46 days before = March 5
    $date = $this->service->computeHolidayDate($holiday, 2025);

    expect($date->toDateString())->toBe('2025-03-05');
});

it('computes major easter relative holidays correctly for 2025', function (string $name, int $offset, string $expectedDate) {
    $holiday = Holiday::factory()->easterRelative($offset)->create([
        'name' => $name,
    ]);

    $date = $this->service->computeHolidayDate($holiday, 2025);

    expect($date->toDateString())->toBe($expectedDate);
})->with([
    'Ash Wednesday' => ['Ash Wednesday', -46, '2025-03-05'],
    'Palm Sunday' => ['Palm Sunday', -7, '2025-04-13'],
    'Holy Thursday' => ['Holy Thursday', -3, '2025-04-17'],
    'Good Friday' => ['Good Friday', -2, '2025-04-18'],
    'Holy Saturday' => ['Holy Saturday', -1, '2025-04-19'],
    'Easter Sunday' => ['Easter Sunday', 0, '2025-04-20'],
    'Divine Mercy Sunday' => ['Divine Mercy Sunday', 7, '2025-04-27'],
    'Ascension' => ['Ascension', 39, '2025-05-29'],
    'Pentecost' => ['Pentecost', 49, '2025-06-08'],
    'Trinity Sunday' => ['Trinity Sunday', 56, '2025-06-15'],
    'Corpus Christi' => ['Corpus Christi', 60, '2025-06-19'],
]);

/*
|--------------------------------------------------------------------------
| Computed Holiday Tests (Nth Day of Week in Month)
|--------------------------------------------------------------------------
*/

it('computes nth day of week in month correctly', function () {
    // 4th Thursday of November (Thanksgiving US)
    $holiday = Holiday::factory()->computed(['nth' => 4, 'day' => 'thursday', 'month' => 11])->create([
        'name' => 'Thanksgiving',
    ]);

    // 2025: November 4th Thursday = November 27
    $date = $this->service->computeHolidayDate($holiday, 2025);

    expect($date->toDateString())->toBe('2025-11-27');
});

it('computes first day of week in month correctly', function () {
    // 1st Sunday of Advent (depends on Christmas calculation, but test with a simple rule)
    $holiday = Holiday::factory()->computed(['nth' => 1, 'day' => 'sunday', 'month' => 12])->create([
        'name' => 'First Sunday of December',
    ]);

    // 2025: First Sunday of December = December 7
    $date = $this->service->computeHolidayDate($holiday, 2025);

    expect($date->toDateString())->toBe('2025-12-07');
});

it('computes last day of week in month correctly', function () {
    // Last Monday of May (Memorial Day US)
    $holiday = Holiday::factory()->computed(['last' => true, 'day' => 'monday', 'month' => 5])->create([
        'name' => 'Last Monday of May',
    ]);

    // 2025: Last Monday of May = May 26
    $date = $this->service->computeHolidayDate($holiday, 2025);

    expect($date->toDateString())->toBe('2025-05-26');
});

it('computes last sunday in month correctly', function () {
    // Christ the King - Last Sunday before Advent (simplified: last Sunday of November)
    $holiday = Holiday::factory()->computed(['last' => true, 'day' => 'sunday', 'month' => 11])->create([
        'name' => 'Last Sunday of November',
    ]);

    // 2025: Last Sunday of November = November 30
    $date = $this->service->computeHolidayDate($holiday, 2025);

    expect($date->toDateString())->toBe('2025-11-30');
});

it('throws exception for computed holiday without valid rule', function () {
    $holiday = Holiday::factory()->create([
        'type' => HolidayType::Computed,
        'computation_rule' => null,
    ]);

    $this->service->computeHolidayDate($holiday, 2025);
})->throws(\InvalidArgumentException::class);

it('throws exception for invalid day of week', function () {
    $holiday = Holiday::factory()->computed(['nth' => 1, 'day' => 'notaday', 'month' => 1])->create([
        'name' => 'Invalid Day',
    ]);

    $this->service->computeHolidayDate($holiday, 2025);
})->throws(\InvalidArgumentException::class);

/*
|--------------------------------------------------------------------------
| Holiday Instance Management Tests
|--------------------------------------------------------------------------
*/

it('creates holiday instance when not exists', function () {
    $holiday = Holiday::factory()->fixed(12, 25)->create([
        'name' => 'Christmas Day',
    ]);

    expect(HolidayInstance::count())->toBe(0);

    $instance = $this->service->getOrCreateInstance($holiday, 2025);

    expect($instance)->toBeInstanceOf(HolidayInstance::class);
    expect($instance->year)->toBe(2025);
    expect($instance->date->toDateString())->toBe('2025-12-25');
    expect(HolidayInstance::count())->toBe(1);
});

it('returns existing instance instead of creating new one', function () {
    $holiday = Holiday::factory()->fixed(12, 25)->create([
        'name' => 'Christmas Day',
    ]);

    $existingInstance = HolidayInstance::create([
        'holiday_id' => $holiday->id,
        'year' => 2025,
        'date' => '2025-12-25',
    ]);

    $instance = $this->service->getOrCreateInstance($holiday, 2025);

    expect($instance->id)->toBe($existingInstance->id);
    expect(HolidayInstance::count())->toBe(1);
});

it('computes instances for all holidays in a year', function () {
    Holiday::factory()->fixed(12, 25)->create(['name' => 'Christmas']);
    Holiday::factory()->fixed(1, 1)->create(['name' => 'New Year']);
    Holiday::factory()->easterRelative(0)->create(['name' => 'Easter']);

    $instances = $this->service->computeInstancesForYear(2025);

    expect($instances)->toHaveCount(3);
    expect(HolidayInstance::count())->toBe(3);
});

it('computes instances only for team-available holidays', function () {
    $team = Team::factory()->create();

    // Universal holiday (null team)
    Holiday::factory()->fixed(12, 25)->create([
        'name' => 'Christmas',
        'team_id' => null,
    ]);

    // Team-specific holiday
    Holiday::factory()->fixed(12, 8)->create([
        'name' => 'Team Holiday',
        'team_id' => $team->id,
    ]);

    // Another team's holiday (should not be included)
    $otherTeam = Team::factory()->create();
    Holiday::factory()->fixed(11, 1)->create([
        'name' => 'Other Team Holiday',
        'team_id' => $otherTeam->id,
    ]);

    $instances = $this->service->computeInstancesForYear(2025, $team->id);

    expect($instances)->toHaveCount(2);
});

/*
|--------------------------------------------------------------------------
| Date Range Queries
|--------------------------------------------------------------------------
*/

it('returns holiday instances within date range', function () {
    Holiday::factory()->fixed(12, 25)->create(['name' => 'Christmas']);
    Holiday::factory()->fixed(1, 1)->create(['name' => 'New Year']);
    Holiday::factory()->fixed(8, 15)->create(['name' => 'Assumption']);

    $instances = $this->service->getInstancesInRange('2025-12-01', '2026-01-15');

    expect($instances)->toHaveCount(2);
    expect($instances->pluck('holiday.name')->sort()->values()->all())->toBe(['Christmas', 'New Year']);
});

it('handles date range spanning multiple years', function () {
    Holiday::factory()->fixed(12, 25)->create(['name' => 'Christmas']);

    $instances = $this->service->getInstancesInRange('2024-12-01', '2026-12-31');

    // Should have Christmas for 2024, 2025, and 2026
    expect($instances)->toHaveCount(3);
});

/*
|--------------------------------------------------------------------------
| Holiday Check Functions
|--------------------------------------------------------------------------
*/

it('correctly identifies a date as a holiday', function () {
    Holiday::factory()->fixed(12, 25)->create(['name' => 'Christmas']);

    expect($this->service->isHoliday('2025-12-25'))->toBeTrue();
    expect($this->service->isHoliday('2025-12-26'))->toBeFalse();
});

it('returns holidays on a specific date', function () {
    Holiday::factory()->fixed(12, 25)->create(['name' => 'Christmas']);
    Holiday::factory()->fixed(12, 25)->create(['name' => 'Another Holiday on Dec 25']);

    $holidays = $this->service->getHolidaysOnDate('2025-12-25');

    expect($holidays)->toHaveCount(2);
});

it('correctly identifies holy day of obligation', function () {
    Holiday::factory()->fixed(12, 25)->create([
        'name' => 'Christmas',
        'is_holy_day_of_obligation' => true,
    ]);

    Holiday::factory()->fixed(12, 26)->create([
        'name' => 'St. Stephen',
        'is_holy_day_of_obligation' => false,
    ]);

    expect($this->service->isHolyDayOfObligation('2025-12-25'))->toBeTrue();
    expect($this->service->isHolyDayOfObligation('2025-12-26'))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Cache Clearing Tests
|--------------------------------------------------------------------------
*/

it('clears instances for a specific holiday', function () {
    $holiday = Holiday::factory()->fixed(12, 25)->create(['name' => 'Christmas']);

    $this->service->getOrCreateInstance($holiday, 2024);
    $this->service->getOrCreateInstance($holiday, 2025);
    $this->service->getOrCreateInstance($holiday, 2026);

    expect(HolidayInstance::count())->toBe(3);

    $this->service->clearInstancesForHoliday($holiday);

    expect(HolidayInstance::count())->toBe(0);
});

it('clears instances for a specific year', function () {
    Holiday::factory()->fixed(12, 25)->create(['name' => 'Christmas']);
    Holiday::factory()->fixed(1, 1)->create(['name' => 'New Year']);

    $this->service->computeInstancesForYear(2024);
    $this->service->computeInstancesForYear(2025);

    expect(HolidayInstance::count())->toBe(4);

    $this->service->clearInstancesForYear(2025);

    expect(HolidayInstance::count())->toBe(2);
    expect(HolidayInstance::where('year', 2024)->count())->toBe(2);
    expect(HolidayInstance::where('year', 2025)->count())->toBe(0);
});

it('clears easter cache when clearing year instances', function () {
    Cache::flush();

    // Populate Easter cache
    $this->service->getEasterDate(2025);
    expect(Cache::has('easter_date_2025'))->toBeTrue();

    // Clear year instances
    $this->service->clearInstancesForYear(2025);

    expect(Cache::has('easter_date_2025'))->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Edge Cases
|--------------------------------------------------------------------------
*/

it('handles leap year correctly for fixed dates', function () {
    $holiday = Holiday::factory()->fixed(2, 28)->create(['name' => 'Feb 28 Holiday']);

    // Non-leap year
    expect($this->service->computeHolidayDate($holiday, 2025)->toDateString())->toBe('2025-02-28');

    // Leap year
    expect($this->service->computeHolidayDate($holiday, 2024)->toDateString())->toBe('2024-02-28');
});

it('handles easter that falls in march', function () {
    // Easter 2024 is March 31
    $easterHoliday = Holiday::factory()->easterRelative(0)->create(['name' => 'Easter']);

    $date = $this->service->computeHolidayDate($easterHoliday, 2024);

    expect($date->month)->toBe(3);
    expect($date->toDateString())->toBe('2024-03-31');
});

it('handles ash wednesday in february', function () {
    // Easter 2025 is April 20, so Ash Wednesday is March 5
    // Easter 2024 is March 31, so Ash Wednesday is February 14
    $ashWednesday = Holiday::factory()->easterRelative(-46)->create(['name' => 'Ash Wednesday']);

    expect($this->service->computeHolidayDate($ashWednesday, 2025)->toDateString())->toBe('2025-03-05');
    expect($this->service->computeHolidayDate($ashWednesday, 2024)->toDateString())->toBe('2024-02-14');
});

it('computes all days of week correctly', function (string $day, int $expectedDayNumber) {
    $holiday = Holiday::factory()->computed(['nth' => 1, 'day' => $day, 'month' => 1])->create([
        'name' => "First {$day} of January",
    ]);

    $date = $this->service->computeHolidayDate($holiday, 2025);

    expect($date->dayOfWeek)->toBe($expectedDayNumber);
})->with([
    'sunday' => ['sunday', Carbon::SUNDAY],
    'monday' => ['monday', Carbon::MONDAY],
    'tuesday' => ['tuesday', Carbon::TUESDAY],
    'wednesday' => ['wednesday', Carbon::WEDNESDAY],
    'thursday' => ['thursday', Carbon::THURSDAY],
    'friday' => ['friday', Carbon::FRIDAY],
    'saturday' => ['saturday', Carbon::SATURDAY],
]);
