<?php

use App\Models\Team;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Models\Holiday;
use App\Enums\DayPattern;
use App\Models\ScheduleRule;
use App\Enums\ScheduleAction;
use Illuminate\Support\Carbon;
use App\Models\AdorationChapel;
use App\Models\HolidayInstance;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\ScheduleOverride;
use App\Models\LiturgicalSchedule;
use App\Enums\ScheduleRuleCondition;
use App\Models\AdorationChapelSchedule;
use App\Services\ActivityScheduleService;
use App\DataTransferObjects\ComputedActivity;

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

/*
|--------------------------------------------------------------------------
| Activity Type Filtering Tests
|--------------------------------------------------------------------------
*/

it('filters activities by type', function () {
    // Mass schedule
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Confession schedule
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '16:00',
        'type' => 'confession',
    ]);

    // Adoration schedule
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '08:00',
        'type' => 'adoration',
    ]);

    // Filter for mass only
    $massOnly = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-06'),
        activityTypes: ['mass']
    );

    expect($massOnly)->toHaveCount(1);
    expect($massOnly->first()->type)->toBe('mass');

    // Filter for mass and confession
    $massAndConfession = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-06'),
        activityTypes: ['mass', 'confession']
    );

    expect($massAndConfession)->toHaveCount(2);
    $types = $massAndConfession->pluck('type')->sort()->values();
    expect($types[0])->toBe('confession');
    expect($types[1])->toBe('mass');

    // No filter returns all
    $all = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-06')
    );

    expect($all)->toHaveCount(3);
});

/*
|--------------------------------------------------------------------------
| Multiple Churches Tests
|--------------------------------------------------------------------------
*/

it('retrieves activities for multiple churches on a date', function () {
    $church2 = Church::factory()->create(['team_id' => $this->team->id]);

    // Church 1 mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Church 2 mass
    ActivitySchedule::factory()->create([
        'church_id' => $church2->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '09:00',
        'type' => 'mass',
    ]);

    $activities = $this->service->getActivitiesForChurchesOnDate(
        collect([$this->church, $church2]),
        Carbon::parse('2025-01-06')
    );

    expect($activities)->toHaveCount(2);
    $times = $activities->pluck('startTime')->sort()->values();
    expect($times[0])->toBe('07:00');
    expect($times[1])->toBe('09:00');
});

/*
|--------------------------------------------------------------------------
| Date Range Tests
|--------------------------------------------------------------------------
*/

it('retrieves activities for a date range', function () {
    // Daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // One-time activity on Jan 7
    OneTimeActivity::factory()->create([
        'church_id' => $this->church->id,
        'date' => '2025-01-07',
        'start_time' => '14:00',
        'type' => 'mass',
    ]);

    $activities = $this->service->getActivitiesForChurchInRange(
        $this->church,
        Carbon::parse('2025-01-06'),
        Carbon::parse('2025-01-08')
    );

    // Returns collection keyed by date
    expect($activities)->toHaveCount(3);
    expect($activities->keys()->all())->toBe(['2025-01-06', '2025-01-07', '2025-01-08']);

    // Jan 6: 1 mass
    expect($activities['2025-01-06'])->toHaveCount(1);

    // Jan 7: 1 regular mass + 1 one-time
    expect($activities['2025-01-07'])->toHaveCount(2);

    // Jan 8: 1 mass
    expect($activities['2025-01-08'])->toHaveCount(1);
});

/*
|--------------------------------------------------------------------------
| Helper Method Tests
|--------------------------------------------------------------------------
*/

it('checks if church has activities on a date', function () {
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Sunday,
        'start_time' => '10:00',
        'type' => 'mass',
    ]);

    // Sunday has activities
    $hasSunday = $this->service->churchHasActivitiesOnDate(
        $this->church,
        Carbon::parse('2025-01-05') // Sunday
    );
    expect($hasSunday)->toBeTrue();

    // Monday has no activities
    $hasMonday = $this->service->churchHasActivitiesOnDate(
        $this->church,
        Carbon::parse('2025-01-06') // Monday
    );
    expect($hasMonday)->toBeFalse();
});

it('finds next date with activities', function () {
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Sunday,
        'start_time' => '10:00',
        'type' => 'mass',
    ]);

    // Starting from Monday Jan 6, next Sunday is Jan 12
    $nextDate = $this->service->getNextDateWithActivities(
        $this->church,
        Carbon::parse('2025-01-06')
    );

    expect($nextDate)->not->toBeNull();
    expect($nextDate->toDateString())->toBe('2025-01-12');
});

it('returns null when no activities within max days', function () {
    // No schedules created

    $nextDate = $this->service->getNextDateWithActivities(
        $this->church,
        Carbon::parse('2025-01-06'),
        maxDaysToSearch: 7
    );

    expect($nextDate)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Search Activities Tests
|--------------------------------------------------------------------------
*/

it('searches activities across churches', function () {
    $church2 = Church::factory()->create([
        'team_id' => $this->team->id,
        'name' => 'St. Mary Cathedral',
        'district' => 'Downtown',
    ]);

    // Church 1 in different district
    $this->church->update(['district' => 'Suburbs', 'name' => 'Holy Family']);

    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    ActivitySchedule::factory()->create([
        'church_id' => $church2->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '09:00',
        'type' => 'mass',
    ]);

    // Search all masses
    $allMasses = $this->service->searchActivities(
        Carbon::parse('2025-01-06'),
        ['mass']
    );
    expect($allMasses)->toHaveCount(2);

    // Search by district
    $downtownMasses = $this->service->searchActivities(
        Carbon::parse('2025-01-06'),
        ['mass'],
        district: 'Downtown'
    );
    expect($downtownMasses)->toHaveCount(1);
    expect($downtownMasses->first()->startTime)->toBe('09:00');

    // Search by church name
    $maryMasses = $this->service->searchActivities(
        Carbon::parse('2025-01-06'),
        ['mass'],
        churchName: 'Mary'
    );
    expect($maryMasses)->toHaveCount(1);

    // Filter by start time
    $laterMasses = $this->service->searchActivities(
        Carbon::parse('2025-01-06'),
        ['mass'],
        startTime: '08:00'
    );
    expect($laterMasses)->toHaveCount(1);
    expect($laterMasses->first()->startTime)->toBe('09:00');
});

it('searches activities case-insensitively', function (string $filterType, string $filterValue) {
    $church = Church::factory()->create([
        'team_id' => $this->team->id,
        'name' => 'Christ The King',
        'district' => 'Kampala',
        'city' => 'Kampala City',
    ]);

    ActivitySchedule::factory()->create([
        'church_id' => $church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '08:00',
        'type' => 'mass',
    ]);

    $params = [$filterType => $filterValue];

    $results = $this->service->searchActivities(
        Carbon::parse('2025-01-06'),
        ['mass'],
        churchName: $params['churchName'] ?? null,
        district: $params['district'] ?? null,
    );

    expect($results)->toHaveCount(1);
})->with([
    'lowercase church name' => ['churchName', 'christ the king'],
    'uppercase church name' => ['churchName', 'CHRIST THE KING'],
    'lowercase district' => ['district', 'kampala'],
    'uppercase city' => ['district', 'KAMPALA CITY'],
]);

/*
|--------------------------------------------------------------------------
| Diocese-Level Rule Tests
|--------------------------------------------------------------------------
*/

it('applies diocese-level rules to all churches in diocese', function () {
    $diocese = Diocese::factory()->create(['team_id' => $this->team->id]);
    $parish = Parish::factory()->create([
        'diocese_id' => $diocese->id,
        'team_id' => $this->team->id,
    ]);

    // Update church to belong to parish (and thus diocese)
    $this->church->update([
        'parishable_type' => Parish::class,
        'parishable_id' => $parish->id,
    ]);

    // Daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Diocese-level rule to exclude on public holidays
    ScheduleRule::factory()->create([
        'scope_type' => 'diocese',
        'scope_id' => $diocese->id,
        'condition_type' => ScheduleRuleCondition::PublicHoliday,
        'action' => ScheduleAction::Exclude,
    ]);

    // Create holiday
    Holiday::factory()->fixed(1, 1)->create(['name' => 'New Year']);

    // On holiday: diocese rule should exclude
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-01')
    );

    expect($activities)->toHaveCount(0);

    // On normal day: should have mass
    $normalActivities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-01-02')
    );

    expect($normalActivities)->toHaveCount(1);
});

/*
|--------------------------------------------------------------------------
| Holiday Info Tests
|--------------------------------------------------------------------------
*/

it('returns holiday info for a date', function () {
    $holiday = Holiday::factory()->fixed(12, 25)->create(['name' => 'Christmas Day']);
    HolidayInstance::factory()->create([
        'holiday_id' => $holiday->id,
        'year' => 2025,
        'date' => '2025-12-25',
    ]);

    $info = $this->service->getHolidayInfoForDate(Carbon::parse('2025-12-25'));

    expect($info['is_public_holiday'])->toBeTrue();
    expect($info['holidays'])->toHaveCount(1);
    expect($info['holidays'][0]['name'])->toBe('Christmas Day');

    // Normal day
    $normalInfo = $this->service->getHolidayInfoForDate(Carbon::parse('2025-12-26'));
    expect($normalInfo['is_public_holiday'])->toBeFalse();
    expect($normalInfo['holidays'])->toBeEmpty();
});

/*
|--------------------------------------------------------------------------
| Adoration Chapel Search Tests
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Holiday Schedule Suppression Tests
|--------------------------------------------------------------------------
*/

it('suppresses regular schedules on a suppressing holiday', function () {
    // Regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Good Friday holiday (suppresses regular schedules)
    // Easter 2025 is April 20, Good Friday is April 18
    Holiday::factory()->goodFriday()->create();

    // On Good Friday: regular mass should be suppressed
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-04-18')
    );

    expect($activities)->toHaveCount(0);
});

it('still shows liturgical schedules on a suppressing holiday', function () {
    // Regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Good Friday holiday (suppresses regular schedules)
    $goodFriday = Holiday::factory()->goodFriday()->create();

    // Create holiday instance for 2025
    HolidayInstance::factory()->create([
        'holiday_id' => $goodFriday->id,
        'year' => 2025,
        'date' => '2025-04-18',
    ]);

    // Liturgical schedule for Good Friday
    $litSchedule = LiturgicalSchedule::factory()->create([
        'church_id' => $this->church->id,
        'holiday_id' => $goodFriday->id,
        'team_id' => $this->team->id,
        'replaces_regular_schedule' => true,
    ]);

    $litSchedule->times()->create([
        'start_time' => '15:00',
        'language' => 'English',
        'type' => 'mass',
    ]);

    // On Good Friday: only liturgical schedule should show
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-04-18')
    );

    expect($activities)->toHaveCount(1);
    expect($activities->first()->startTime)->toBe('15:00');
    expect($activities->first()->source)->toBe('liturgical');
});

it('still shows one-time activities on a suppressing holiday', function () {
    // Regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Good Friday holiday
    Holiday::factory()->goodFriday()->create();

    // One-time activity on Good Friday
    OneTimeActivity::factory()->create([
        'church_id' => $this->church->id,
        'date' => '2025-04-18',
        'start_time' => '18:00',
        'type' => 'mass',
        'activity_type_description' => 'Stations of the Cross',
    ]);

    // On Good Friday: only one-time activity should show (regular suppressed)
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-04-18')
    );

    expect($activities)->toHaveCount(1);
    expect($activities->first()->startTime)->toBe('18:00');
    expect($activities->first()->source)->toBe('one_time');
});

it('does not suppress regular schedules on non-suppressing holidays', function () {
    // Regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Christmas does NOT suppress regular schedules
    Holiday::factory()->christmas()->create();

    // On Christmas: regular mass should still appear
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-12-25')
    );

    expect($activities)->toHaveCount(1);
    expect($activities->first()->startTime)->toBe('07:00');
});

it('bypasses schedule rules on a suppressing holiday', function () {
    // Regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Rule that would normally exclude on holidays
    ScheduleRule::factory()->create([
        'scope_type' => 'church',
        'scope_id' => $this->church->id,
        'condition_type' => ScheduleRuleCondition::PublicHoliday,
        'action' => ScheduleAction::Exclude,
    ]);

    // Good Friday (suppressing) — rules should be bypassed entirely,
    // and regular schedules suppressed by the holiday flag, not rules
    Holiday::factory()->goodFriday()->create();

    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-04-18')
    );

    // No activities because regular schedules are suppressed (not because of rules)
    expect($activities)->toHaveCount(0);
});

it('bypasses schedule overrides on a suppressing holiday', function () {
    // Regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Override that would normally modify the time
    ScheduleOverride::factory()->create([
        'scope_type' => 'church',
        'scope_id' => $this->church->id,
        'start_date' => '2025-04-18',
        'end_date' => '2025-04-18',
        'action' => ScheduleAction::Modify,
        'replacement_times' => [
            ['original' => '07:00', 'replacement' => '08:00'],
        ],
    ]);

    // Good Friday (suppressing) — override should be bypassed
    Holiday::factory()->goodFriday()->create();

    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-04-18')
    );

    // No activities — regular schedules suppressed, override never applied
    expect($activities)->toHaveCount(0);
});

it('shows no activities when church has no liturgical schedule for suppressing holiday', function () {
    // Regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);

    // Holy Thursday (suppressing)
    Holiday::factory()->holyThursday()->create();

    // No liturgical schedule for this church on Holy Thursday
    // Easter 2025 = April 20, Holy Thursday = April 17
    $activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-04-17')
    );

    expect($activities)->toHaveCount(0);
});

it('handles multiple churches differently on a suppressing holiday', function () {
    $church2 = Church::factory()->create(['team_id' => $this->team->id]);

    // Both churches have regular daily mass
    ActivitySchedule::factory()->create([
        'church_id' => $this->church->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '07:00',
        'type' => 'mass',
    ]);
    ActivitySchedule::factory()->create([
        'church_id' => $church2->id,
        'day_pattern' => DayPattern::Daily,
        'start_time' => '09:00',
        'type' => 'mass',
    ]);

    // Good Friday (suppressing)
    $goodFriday = Holiday::factory()->goodFriday()->create();
    HolidayInstance::factory()->create([
        'holiday_id' => $goodFriday->id,
        'year' => 2025,
        'date' => '2025-04-18',
    ]);

    // Only church2 has liturgical schedule for Good Friday
    $litSchedule = LiturgicalSchedule::factory()->create([
        'church_id' => $church2->id,
        'holiday_id' => $goodFriday->id,
        'team_id' => $this->team->id,
        'replaces_regular_schedule' => true,
    ]);

    $litSchedule->times()->create([
        'start_time' => '15:00',
        'type' => 'mass',
        'language' => 'English',
    ]);

    // Church 1: no activities (no liturgical schedule)
    $church1Activities = $this->service->getActivitiesForChurchOnDate(
        $this->church,
        Carbon::parse('2025-04-18')
    );
    expect($church1Activities)->toHaveCount(0);

    // Church 2: only liturgical schedule
    $church2Activities = $this->service->getActivitiesForChurchOnDate(
        $church2,
        Carbon::parse('2025-04-18')
    );
    expect($church2Activities)->toHaveCount(1);
    expect($church2Activities->first()->startTime)->toBe('15:00');
    expect($church2Activities->first()->source)->toBe('liturgical');
});

/*
|--------------------------------------------------------------------------
| Adoration Chapel Search Tests
|--------------------------------------------------------------------------
*/

it('includes adoration chapels in search results', function (bool $is24Hours, ?string $startTime) {
    $chapel = AdorationChapel::factory()->create([
        'church_id' => $this->church->id,
        'team_id' => $this->team->id,
    ]);

    if ($is24Hours) {
        AdorationChapelSchedule::factory()->daily()->open24Hours()->create([
            'adoration_chapel_id' => $chapel->id,
        ]);
    } else {
        AdorationChapelSchedule::factory()->daily()->hours('06:00', '21:00')->create([
            'adoration_chapel_id' => $chapel->id,
        ]);
    }

    $results = $this->service->searchActivities(
        Carbon::parse('2025-01-06'),
        ['adoration'],
        startTime: $startTime,
    );

    expect($results)->toHaveCount(1);
    expect($results->first()->source)->toBe(ComputedActivity::SOURCE_ADORATION_CHAPEL);
})->with([
    'before opening hours' => [false, '04:00'],
    'during opening hours' => [false, '10:00'],
    'without start time filter' => [false, null],
    '24-hour chapel at late hour' => [true, '23:00'],
    '24-hour chapel without start time' => [true, null],
]);

it('excludes adoration chapels from search results', function (bool $isClosed, ?string $startTime) {
    $chapel = AdorationChapel::factory()->create([
        'church_id' => $this->church->id,
        'team_id' => $this->team->id,
    ]);

    if ($isClosed) {
        AdorationChapelSchedule::factory()->daily()->closed()->create([
            'adoration_chapel_id' => $chapel->id,
        ]);
    } else {
        AdorationChapelSchedule::factory()->daily()->hours('06:00', '21:00')->create([
            'adoration_chapel_id' => $chapel->id,
        ]);
    }

    $results = $this->service->searchActivities(
        Carbon::parse('2025-01-06'),
        ['adoration'],
        startTime: $startTime,
    );

    expect($results)->toHaveCount(0);
})->with([
    'chapel already closed' => [false, '21:00'],
    'chapel past closing time' => [false, '22:00'],
    'closed schedule without start time' => [true, null],
    'closed schedule with start time' => [true, '10:00'],
]);
