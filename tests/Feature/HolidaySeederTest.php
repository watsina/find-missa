<?php

use Carbon\Carbon;
use App\Models\Holiday;
use App\Models\HolidayInstance;
use Database\Seeders\HolidaySeeder;

/*
|--------------------------------------------------------------------------
| Holiday Seeder Tests
|--------------------------------------------------------------------------
*/

it('creates all universal catholic holidays', function () {
    $this->seed(HolidaySeeder::class);

    expect(Holiday::count())->toBeGreaterThan(0);
    expect(Holiday::where('is_universal', true)->count())->toBe(Holiday::count());
});

it('is idempotent when run multiple times', function () {
    $this->seed(HolidaySeeder::class);
    $countAfterFirst = Holiday::count();

    $this->seed(HolidaySeeder::class);
    $countAfterSecond = Holiday::count();

    expect($countAfterSecond)->toBe($countAfterFirst);
});

it('regenerates holiday instances for current and next year after seeding', function () {
    $this->seed(HolidaySeeder::class);

    $currentYear = (int) now()->year;
    $nextYear = $currentYear + 1;

    $currentYearInstances = HolidayInstance::where('year', $currentYear)->count();
    $nextYearInstances = HolidayInstance::where('year', $nextYear)->count();

    expect($currentYearInstances)->toBeGreaterThan(0);
    expect($nextYearInstances)->toBeGreaterThan(0);
});

it('corrects stale holiday instances when definitions change', function () {
    // Seed holidays first
    $this->seed(HolidaySeeder::class);

    $currentYear = (int) now()->year;

    // Simulate a stale instance by corrupting Holy Thursday's date
    $holyThursday = Holiday::where('slug', 'holy-thursday')->first();
    $instance = HolidayInstance::where('holiday_id', $holyThursday->id)
        ->where('year', $currentYear)
        ->first();

    $correctDate = $instance->date->toDateString();

    // Corrupt the date to Easter Sunday (the bug we encountered)
    $easterSunday = Holiday::where('slug', 'easter-sunday')->first();
    $easterInstance = HolidayInstance::where('holiday_id', $easterSunday->id)
        ->where('year', $currentYear)
        ->first();

    $instance->update(['date' => $easterInstance->date]);

    expect($instance->fresh()->date->toDateString())->toBe($easterInstance->date->toDateString());

    // Re-run seeder — should regenerate with correct date
    $this->seed(HolidaySeeder::class);

    $refreshedInstance = HolidayInstance::where('holiday_id', $holyThursday->id)
        ->where('year', $currentYear)
        ->first();

    expect($refreshedInstance->date->toDateString())->toBe($correctDate);
    expect($refreshedInstance->date->dayOfWeek)->toBe(Carbon::THURSDAY);
});

it('sets suppresses_regular_schedule on triduum holidays', function () {
    $this->seed(HolidaySeeder::class);

    $triduum = Holiday::whereIn('slug', ['holy-thursday', 'good-friday', 'holy-saturday'])->get();

    expect($triduum)->toHaveCount(3);
    $triduum->each(function (Holiday $holiday) {
        expect($holiday->suppresses_regular_schedule)->toBeTrue();
    });
});

it('does not set suppresses_regular_schedule on non-triduum holidays', function () {
    $this->seed(HolidaySeeder::class);

    $nonTriduum = Holiday::whereNotIn('slug', ['holy-thursday', 'good-friday', 'holy-saturday'])->get();

    expect($nonTriduum->count())->toBeGreaterThan(0);
    $nonTriduum->each(function (Holiday $holiday) {
        expect($holiday->suppresses_regular_schedule)->toBeFalse();
    });
});
