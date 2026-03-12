<?php

namespace Tests\Feature\Services;

use App\Models\Team;
use App\Models\Church;
use App\Enums\DayPattern;
use App\Enums\ScheduleScope;
use App\Models\ActivityTime;
use App\Enums\ScheduleAction;
use Illuminate\Support\Carbon;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\ScheduleOverride;
use App\Enums\ActivityOccurrence;
use App\Models\ScheduleDayDetail;
use App\Models\ActivityTimeException;
use App\Services\ActivityTimeMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ActivityTimeMigrationService;
    $this->church = Church::factory()->create();
});

test('it migrates one-time activities correctly', function () {
    $source = ActivityTime::factory()->create([
        'church_id' => $this->church->id,
        'occurrence' => ActivityOccurrence::OneTime,
        'date' => Carbon::today()->addDays(5),
        'start_time' => '10:00:00',
        'type' => 'mass',
    ]);

    $this->service->migrateChurch($this->church);

    expect(OneTimeActivity::count())->toBe(1);
    $migrated = OneTimeActivity::first();

    expect($migrated->church_id)->toBe($source->church_id)
        ->and($migrated->date->toDateString())->toBe($source->date->toDateString())
        ->and($migrated->start_time)->toBe('10:00'); // Updated to match Timecast format
});

test('it migrates daily activities correctly', function () {
    $source = ActivityTime::factory()->create([
        'church_id' => $this->church->id,
        'occurrence' => ActivityOccurrence::Daily,
        'start_time' => '08:00:00',
    ]);

    $this->service->migrateChurch($this->church);

    expect(ActivitySchedule::count())->toBe(1);
    $migrated = ActivitySchedule::first();

    expect($migrated->day_pattern)->toBe(DayPattern::Daily)
        ->and($migrated->start_time)->toBe('08:00'); // Updated to match Timecast format
});

test('it groups weekly activities into specific day patterns', function () {
    // Ensure all activities share the same team so they group together
    // Team factory doesn't accept church_id, but ActivityTime factory creates one anyway
    // So we'll use a manually created team ID
    $team = Team::factory()->create();

    // Create 3 weekly records: Mon, Wed, Fri at same time
    $commonData = [
        'church_id' => $this->church->id,
        'team_id' => $team->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'start_time' => '09:00:00',
        'type' => 'mass',
        'language' => 'en',
    ];

    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Monday']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Wednesday']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Friday']));

    // Create one different one (different time) - can use same team
    ActivityTime::factory()->create(array_merge($commonData, ['start_time' => '18:00:00', 'day_of_week' => 'Monday']));

    $this->service->migrateChurch($this->church);

    // Expect 2 schedules: one for 9am (M/W/F) and one for 6pm (M)
    expect(ActivitySchedule::count())->toBe(2);

    $schedules = ActivitySchedule::all();
    $morning = $schedules->first(fn ($s) => $s->start_time === '09:00'); // Updated to match Timecast
    $evening = $schedules->first(fn ($s) => $s->start_time === '18:00'); // Updated to match Timecast

    expect($morning)->not->toBeNull()
        ->and($morning->day_pattern)->toBe(DayPattern::Specific)
        ->and($morning->days_of_week)->toHaveCount(3)
        ->and($morning->days_of_week)->toContain('Monday', 'Wednesday', 'Friday');

    expect($evening)->not->toBeNull()
        ->and($evening->day_pattern)->toBe(DayPattern::Specific)
        ->and($evening->days_of_week)->toHaveCount(1)
        ->and($evening->days_of_week)->toContain('Monday');
});

test('it migrates monthly-nth activities correctly', function () {
    $source = ActivityTime::factory()->create([
        'church_id' => $this->church->id,
        'occurrence' => ActivityOccurrence::MonthlyNthWeekday,
        'week_of_month' => 1,
        'day_of_week' => 'Sunday',
        'start_time' => '11:00:00',
    ]);

    $this->service->migrateChurch($this->church);

    expect(ActivitySchedule::count())->toBe(1);
    $migrated = ActivitySchedule::first();

    expect($migrated->day_pattern)->toBe(DayPattern::MonthlyNth)
        ->and($migrated->week_of_month)->toBe(1)
        ->and($migrated->day_of_week)->toBe('Sunday');
});

test('it migrates exceptions to schedule overrides', function () {
    $activity = ActivityTime::factory()->create([
        'church_id' => $this->church->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'start_time' => '07:00:00',
        'day_of_week' => 'Monday',
    ]);

    ActivityTimeException::factory()->create([
        'activity_time_id' => $activity->id,
        'start_date' => '2025-12-25',
        'end_date' => '2025-12-25',
        'reason' => 'Christmas',
    ]);

    $this->service->migrateChurch($this->church);

    expect(ScheduleOverride::count())->toBe(1);
    $override = ScheduleOverride::first();

    expect($override->scope_type)->toBe(ScheduleScope::Church)
        ->and($override->scope_id)->toBe($this->church->id)
        ->and($override->action)->toBe(ScheduleAction::Exclude)
        ->and($override->target_times)->toContain('07:00')
        ->and($override->start_date->toDateString())->toBe('2025-12-25');
});

test('it handles force migration by clearing old data', function () {
    // Migrate once
    ActivityTime::factory()->create(['church_id' => $this->church->id, 'occurrence' => ActivityOccurrence::Daily]);
    $this->service->migrateChurch($this->church);
    expect(ActivitySchedule::count())->toBe(1);

    // Migrate again with force=true
    $this->service->migrateChurch($this->church, true);

    // Should still be 1, not 2 (duplicates avoided by clearing)
    expect(ActivitySchedule::count())->toBe(1);
});

test('it detects weekdays pattern (Mon-Fri)', function () {
    $team = Team::factory()->create();
    $commonData = [
        'church_id' => $this->church->id,
        'team_id' => $team->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'start_time' => '07:00:00',
        'type' => 'mass',
        'language' => 'English',
    ];

    foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
        ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => $day]));
    }

    $this->service->migrateChurch($this->church);

    expect(ActivitySchedule::count())->toBe(1);
    $schedule = ActivitySchedule::first();
    expect($schedule->day_pattern)->toBe(DayPattern::Weekdays)
        ->and($schedule->days_of_week)->toBeNull()
        ->and($schedule->has_day_variations)->toBeFalse()
        ->and($schedule->language)->toBe('English');
});

test('it detects saturday pattern', function () {
    ActivityTime::factory()->create([
        'church_id' => $this->church->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'day_of_week' => 'Saturday',
        'start_time' => '17:00:00',
        'type' => 'mass',
    ]);

    $this->service->migrateChurch($this->church);

    $schedule = ActivitySchedule::first();
    expect($schedule->day_pattern)->toBe(DayPattern::Saturday)
        ->and($schedule->days_of_week)->toBeNull();
});

test('it detects sunday pattern', function () {
    ActivityTime::factory()->create([
        'church_id' => $this->church->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'day_of_week' => 'Sunday',
        'start_time' => '10:00:00',
        'type' => 'mass',
    ]);

    $this->service->migrateChurch($this->church);

    $schedule = ActivitySchedule::first();
    expect($schedule->day_pattern)->toBe(DayPattern::Sunday)
        ->and($schedule->days_of_week)->toBeNull();
});

test('it creates day variations for weekdays with different languages', function () {
    $team = Team::factory()->create();
    $commonData = [
        'church_id' => $this->church->id,
        'team_id' => $team->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'start_time' => '07:00:00',
        'type' => 'mass',
    ];

    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Monday', 'language' => 'English']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Tuesday', 'language' => 'Swahili']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Wednesday', 'language' => 'English']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Thursday', 'language' => 'Luganda']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Friday', 'language' => 'English']));

    $this->service->migrateChurch($this->church);

    // Should create 1 schedule with day variations
    expect(ActivitySchedule::count())->toBe(1);

    $schedule = ActivitySchedule::first();
    expect($schedule->day_pattern)->toBe(DayPattern::Weekdays)
        ->and($schedule->has_day_variations)->toBeTrue()
        ->and($schedule->language)->toBe('English'); // Default language is English

    // Should have only 2 day detail records (variations from default)
    expect(ScheduleDayDetail::count())->toBe(2);

    expect(ScheduleDayDetail::where('day_of_week', 'Tuesday')->first()->language)->toBe('Swahili');
    expect(ScheduleDayDetail::where('day_of_week', 'Thursday')->first()->language)->toBe('Luganda');
});

test('it splits all 7 days into Weekdays, Saturday, and Sunday schedules', function () {
    $team = Team::factory()->create();
    $commonData = [
        'church_id' => $this->church->id,
        'team_id' => $team->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'start_time' => '06:00:00',
        'type' => 'mass',
        'language' => 'English',
    ];

    foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
        ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => $day]));
    }

    $this->service->migrateChurch($this->church);

    // Should create 3 schedules: Weekdays + Saturday + Sunday
    expect(ActivitySchedule::count())->toBe(3);

    expect(ActivitySchedule::where('day_pattern', DayPattern::Weekdays)->exists())->toBeTrue();
    expect(ActivitySchedule::where('day_pattern', DayPattern::Saturday)->exists())->toBeTrue();
    expect(ActivitySchedule::where('day_pattern', DayPattern::Sunday)->exists())->toBeTrue();
});

test('it splits Mon-Sat into Weekdays and Saturday schedules', function () {
    $team = Team::factory()->create();
    $commonData = [
        'church_id' => $this->church->id,
        'team_id' => $team->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'start_time' => '07:00:00',
        'type' => 'mass',
        'language' => 'English',
    ];

    foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day) {
        ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => $day]));
    }

    $this->service->migrateChurch($this->church);

    // Should create 2 schedules: Weekdays + Saturday
    expect(ActivitySchedule::count())->toBe(2);

    $weekdaySchedule = ActivitySchedule::where('day_pattern', DayPattern::Weekdays)->first();
    $saturdaySchedule = ActivitySchedule::where('day_pattern', DayPattern::Saturday)->first();

    expect($weekdaySchedule)->not->toBeNull()
        ->and($weekdaySchedule->language)->toBe('English');

    expect($saturdaySchedule)->not->toBeNull()
        ->and($saturdaySchedule->language)->toBe('English');
});

test('it uses most common language as default when English not present', function () {
    $team = Team::factory()->create();
    $commonData = [
        'church_id' => $this->church->id,
        'team_id' => $team->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'start_time' => '07:00:00',
        'type' => 'mass',
    ];

    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Monday', 'language' => 'Swahili']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Tuesday', 'language' => 'Swahili']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Wednesday', 'language' => 'Swahili']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Thursday', 'language' => 'Luganda']));
    ActivityTime::factory()->create(array_merge($commonData, ['day_of_week' => 'Friday', 'language' => 'Swahili']));

    $this->service->migrateChurch($this->church);

    $schedule = ActivitySchedule::first();
    expect($schedule->language)->toBe('Swahili') // Most common
        ->and($schedule->has_day_variations)->toBeTrue();

    // Only 1 day detail (Thu→Luganda)
    expect(ScheduleDayDetail::count())->toBe(1);
    expect(ScheduleDayDetail::first()->language)->toBe('Luganda');
});
