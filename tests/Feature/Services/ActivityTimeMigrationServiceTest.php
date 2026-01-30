<?php

namespace Tests\Feature\Services;

use App\Models\Church;
use App\Enums\DayPattern;
use App\Models\ActivityTag;
use App\Enums\ScheduleScope;
use App\Models\ActivityTime;
use App\Enums\ScheduleAction;
use Illuminate\Support\Carbon;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\ScheduleOverride;
use App\Enums\ActivityOccurrence;
use App\Models\ActivityTimeException;
use App\Services\ActivityTimeMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new ActivityTimeMigrationService;
    $this->church = Church::factory()->create();
    $this->tag = ActivityTag::factory()->create(['name' => 'English', 'slug' => 'english']);
});

test('it migrates one-time activities correctly', function () {
    $source = ActivityTime::factory()->create([
        'church_id' => $this->church->id,
        'occurrence' => ActivityOccurrence::OneTime,
        'date' => Carbon::today()->addDays(5),
        'start_time' => '10:00:00',
        'type' => 'mass',
    ]);
    $source->tags()->attach($this->tag);

    $this->service->migrateChurch($this->church);

    expect(OneTimeActivity::count())->toBe(1);
    $migrated = OneTimeActivity::first();

    expect($migrated->church_id)->toBe($source->church_id)
        ->and($migrated->date->toDateString())->toBe($source->date->toDateString())
        ->and($migrated->start_time)->toBe('10:00') // Updated to match Timecast format
        ->and($migrated->tags->contains($this->tag))->toBeTrue();
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
    $team = \App\Models\Team::factory()->create();

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
