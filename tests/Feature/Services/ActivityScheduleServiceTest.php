<?php

use App\Models\Team;
use App\Models\Church;
use App\Models\Holiday;
use App\Enums\DayPattern;
use App\Models\ScheduleRule;
use App\Enums\ScheduleAction;
use Illuminate\Support\Carbon;
use App\Models\HolidayInstance;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\ScheduleOverride;
use App\Models\LiturgicalSchedule;
use App\Enums\ScheduleRuleCondition;
use App\Services\ActivityScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ActivityScheduleService::class);

    // Setup basic church and team
    $this->team = Team::factory()->create();
    $this->church = Church::factory()->create([
        'team_id' => $this->team->id,
    ]);
});

/*
|--------------------------------------------------------------------------
| Regular Schedule Tests
|--------------------------------------------------------------------------
*/

it('retrieves regular weekly schedules', function () {
    // Create a Sunday mass schedule
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Sunday,
        'start_time' => '10:00',
        'language' => 'English',
        'type' => 'mass',
    ]);

    // Create a Weekday mass schedule
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Weekdays,
        'start_time' => '07:00',
        'language' => 'English',
        'type' => 'mass',
    ]);

    // Test Sunday (2025-01-05 is a Sunday)
    $sundayActivities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-05')
    );

    expect($sundayActivities)->toHaveCount(1);
    expect($sundayActivities->first()->startTime)->toBe('10:00');

    // Test Monday (2025-01-06)
    $mondayActivities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-06')
    );

    expect($mondayActivities)->toHaveCount(1);
    expect($mondayActivities->first()->startTime)->toBe('07:00');
});

it('applies day specific details', function () {
    $schedule = ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'language' => 'English',
        'has_day_variations' => true,
    ]);

    // Override Tuesday to be in Spanish at 08:00
    $schedule->dayDetails()->create([
        'day_of_week' => 'Tuesday',
        'start_time' => '08:00',
        'language' => 'Spanish',
    ]);

    // Skip Wednesday
    $schedule->dayDetails()->create([
        'day_of_week' => 'Wednesday',
        'is_skipped' => true,
    ]);

    // Test Monday (Default) - 2025-01-06
    $monday = $this->service->getActivitiesForChurchOnDate($this->church, Carbon::parse('2025-01-06'));
    expect($monday)->toHaveCount(1);
    expect($monday->first()->startTime)->toBe('07:00');
    expect($monday->first()->language)->toBe('English');

    // Test Tuesday (Override) - 2025-01-07
    $tuesday = $this->service->getActivitiesForChurchOnDate($this->church, Carbon::parse('2025-01-07'));
    expect($tuesday)->toHaveCount(1);
    expect($tuesday->first()->startTime)->toBe('08:00');
    expect($tuesday->first()->language)->toBe('Spanish');

    // Test Wednesday (Skipped) - 2025-01-08
    $wednesday = $this->service->getActivitiesForChurchOnDate($this->church, Carbon::parse('2025-01-08'));
    expect($wednesday)->toHaveCount(0);
});

it('handles monthly nth patterns', function () {
    // First Friday of the month
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::MonthlyNth,
        'day_of_week' => 'Friday',
        'week_of_month' => 1,
        'start_time' => '18:00',
    ]);

    // 2025-01-03 is the first Friday of Jan 2025
    $firstFriday = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-03')
    );
    expect($firstFriday)->toHaveCount(1);

    // 2025-01-10 is the second Friday
    $secondFriday = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-10')
    );
    expect($secondFriday)->toHaveCount(0);
});

/*
|--------------------------------------------------------------------------
| Rule Tests
|--------------------------------------------------------------------------
*/

it('excludes activities on public holidays via rules', function () {
    // Regular weekday mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
    ]);

    // Rule to exclude on public holidays
    ScheduleRule::factory()->create([
        'scope_type' => 'church',
        'scope_id' => $this->church->id,
        'condition_type' => ScheduleRuleCondition::PublicHoliday,
        'action' => ScheduleAction::Exclude,
    ]);

    // Create a holiday for a specific date
    $holiday = Holiday::factory()->fixed(1, 1)->create(['name' => 'New Year']);

    // Verify date is a holiday (sanity check)
    // We need to ensure the holiday instances are generated for the service to see it
    // The service normally handles this, but we'll check logic flow

    // 2025-01-01 is New Year
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-01')
    );

    expect($activities)->toHaveCount(0);

    // 2025-01-02 is not a holiday
    $activitiesNormal = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-02')
    );

    expect($activitiesNormal)->toHaveCount(1);
});

it('excludes specific times via rules', function () {
    // Two masses: 7am and 6pm
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
    ]);
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '18:00',
    ]);

    // Rule: Exclude 7am mass on public holidays
    ScheduleRule::factory()->create([
        'scope_type' => 'church',
        'scope_id' => $this->church->id,
        'condition_type' => ScheduleRuleCondition::PublicHoliday,
        'target_times' => ['07:00'],
        'action' => ScheduleAction::Exclude,
    ]);

    // Create holiday
    Holiday::factory()->fixed(1, 1)->create(['name' => 'New Year']);

    // On holiday: 7am gone, 6pm remains
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-01')
    );

    expect($activities)->toHaveCount(1);
    expect($activities->first()->startTime)->toBe('18:00');
});

/*
|--------------------------------------------------------------------------
| Override Tests
|--------------------------------------------------------------------------
*/

it('excludes activities via date range overrides', function () {
    // Daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
    ]);

    // Override: No mass Jan 1-7
    ScheduleOverride::factory()->create([
        'scope_type' => 'church',
        'scope_id' => $this->church->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-07',
        'action' => ScheduleAction::Exclude,
    ]);

    // Jan 5 (inside override)
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-05')
    );
    expect($activities)->toHaveCount(0);

    // Jan 8 (outside override)
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-08')
    );
    expect($activities)->toHaveCount(1);
});

it('modifies times via overrides', function () {
    // Daily mass at 7am
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
    ]);

    // Override: Change 7am to 8am on Jan 1
    ScheduleOverride::factory()->create([
        'scope_type' => 'church',
        'scope_id' => $this->church->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-01-01',
        'action' => ScheduleAction::Modify,
        'replacement_times' => [
            ['original' => '07:00', 'replacement' => '08:00'],
        ],
    ]);

    // Jan 1
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-01')
    );

    expect($activities)->toHaveCount(1);
    expect($activities->first()->startTime)->toBe('08:00');
});

/*
|--------------------------------------------------------------------------
| Liturgical Schedule Tests
|--------------------------------------------------------------------------
*/

it('replaces regular schedule with liturgical schedule', function () {
    // Regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
    ]);

    // Christmas holiday
    $christmas = Holiday::factory()->fixed(12, 25)->create(['name' => 'Christmas']);

    // Create the holiday instance for 2025-12-25
    HolidayInstance::factory()->create([
        'holiday_id' => $christmas->id,
        'year' => 2025,
        'date' => '2025-12-25',
    ]);

    // Liturgical schedule for Christmas (replaces regular)
    $litSchedule = LiturgicalSchedule::factory()->create([
        'church_id' => $this->church->id,
        'holiday_id' => $christmas->id,
        'replaces_regular_schedule' => true,
    ]);

    // Add times to liturgical schedule
    $litSchedule->times()->create([
        'start_time' => '09:00', // Christmas mass is later
        'language' => 'Latin',
        'type' => 'mass',
    ]);

    // 2025-12-25
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-12-25')
    );

    expect($activities)->toHaveCount(1);
    expect($activities->first()->startTime)->toBe('09:00');
    expect($activities->first()->language)->toBe('Latin');
    expect($activities->first()->source)->toBe('liturgical');
});

it('adds to regular schedule with additive liturgical schedule', function () {
    // Regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
    ]);

    // Special Feast Day
    $feast = Holiday::factory()->fixed(6, 1)->create(['name' => 'Feast Day']);

    // Create the holiday instance for 2025-06-01
    HolidayInstance::factory()->create([
        'holiday_id' => $feast->id,
        'year' => 2025,
        'date' => '2025-06-01',
    ]);

    // Liturgical schedule (additive)
    $litSchedule = LiturgicalSchedule::factory()->create([
        'church_id' => $this->church->id,
        'holiday_id' => $feast->id,
        'replaces_regular_schedule' => false,
    ]);

    // Special evening mass
    $litSchedule->times()->create([
        'start_time' => '19:00',
        'type' => 'mass',
        'language' => 'English',
    ]);

    // 2025-06-01
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-06-01')
    );

    expect($activities)->toHaveCount(2);
    // Should have both 7am and 7pm
    $times = $activities->pluck('startTime')->sort()->values();
    expect($times[0])->toBe('07:00');
    expect($times[1])->toBe('19:00');
});

/*
|--------------------------------------------------------------------------
| One Time Activity Tests
|--------------------------------------------------------------------------
*/

it('includes one time activities', function () {
    // One time wedding or special event
    OneTimeActivity::factory()->create([
        'church_id' => $this->church->id,
        'date' => '2025-07-15',
        'start_time' => '14:00',
        'type' => 'mass', // Wedding mass
        'activity_type_description' => 'Wedding Mass',
    ]);

    // 2025-07-15
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-07-15')
    );

    expect($activities)->toHaveCount(1);
    expect($activities->first()->activityTypeDescription)->toBe('Wedding Mass');
    expect($activities->first()->source)->toBe('one_time');
});
