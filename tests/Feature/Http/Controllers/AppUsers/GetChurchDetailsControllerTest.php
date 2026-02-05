<?php

namespace Tests\Feature\Http\Controllers\AppUsers;

use App\Models\Church;
use App\Models\Holiday;
use App\Enums\DayPattern;
use App\Models\SchedulePeriod;
use Illuminate\Support\Carbon;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\LiturgicalSchedule;

use function Pest\Laravel\getJson;

use App\Models\LiturgicalScheduleTime;

test('it shows the church details with activity schedules grouped by type', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory(3)->recycle($church)->create(['type' => 'mass']);
    ActivitySchedule::factory(2)->recycle($church)->ofType('adoration')->create();
    ActivitySchedule::factory(1)->recycle($church)->ofType('confession')->create();

    getJson(route('church-details', ['church' => $church->id]))
        ->assertOk()
        ->assertJsonPath('data.details.id', $church->id)
        ->assertJsonStructure([
            'data' => [
                'regular_schedule' => [
                    'mass',
                    'confession',
                    'adoration',
                ],
            ],
        ]);
});

test('it returns regular schedule grouped by day pattern', function () {
    $church = Church::factory()->create();

    // Create Sunday mass
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'start_time' => '10:00',
    ]);

    // Create daily mass
    ActivitySchedule::factory()->recycle($church)->daily()->create([
        'start_time' => '07:00',
    ]);

    // Create monthly mass (first Friday)
    ActivitySchedule::factory()->recycle($church)->monthlyNth('Friday', ActivitySchedule::WEEK_FIRST)->create([
        'start_time' => '18:00',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    // Check regular schedule structure
    $response->assertJsonStructure([
        'data' => [
            'regular_schedule' => [
                'mass' => [
                    'weekdays',
                    'saturday',
                    'sunday',
                    'daily',
                    'specific',
                    'monthly_nth',
                ],
            ],
        ],
    ]);

    // Verify schedules are grouped correctly by day pattern
    $data = $response->json('data.regular_schedule.mass');
    expect($data['sunday'])->toHaveCount(1);
    expect($data['daily'])->toHaveCount(1);
    expect($data['monthly_nth'])->toHaveCount(1);
    expect($data['monthly_nth'][0]['label'])->toBe('First Friday');
});

test('it returns schedule period activities separately from regular schedule', function () {
    $church = Church::factory()->create();

    // Create a current schedule period
    $schedulePeriod = SchedulePeriod::factory()->create([
        'team_id' => $church->team_id,
        'name' => 'Lent',
        'start_date' => Carbon::today()->subDays(5),
        'end_date' => Carbon::today()->addDays(30),
        'is_active' => true,
    ]);

    // Create regular activity (no schedule period)
    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'schedule_period_id' => null,
    ]);

    // Create schedule period specific activity
    ActivitySchedule::factory()->recycle($church)->weekdays()->create([
        'schedule_period_id' => $schedulePeriod->id,
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    // Verify regular schedule has Sunday mass
    $regularSchedule = $response->json('data.regular_schedule.mass');
    expect($regularSchedule['sunday'])->toHaveCount(1);
    expect($regularSchedule['weekdays'])->toHaveCount(0);

    // Verify schedule periods are returned
    $periods = $response->json('data.schedule_periods');
    expect($periods)->toHaveCount(1);
    expect($periods[0]['name'])->toBe('Lent');
    expect($periods[0]['is_current'])->toBeTrue();

    // Verify schedule period has weekday mass
    $periodMass = $periods[0]['activities']['mass'];
    expect($periodMass['weekdays'])->toHaveCount(1);
    expect($periodMass['sunday'])->toHaveCount(0);
});

test('it returns monthly-nth schedules with correct labels', function () {
    $church = Church::factory()->create();

    // Create activities for different weeks of month
    ActivitySchedule::factory()->recycle($church)->monthlyNth('Thursday', ActivitySchedule::WEEK_FIRST, 'adoration')->create([
        'start_time' => '19:00',
    ]);

    ActivitySchedule::factory()->recycle($church)->monthlyNth('Friday', ActivitySchedule::WEEK_LAST, 'adoration')->create([
        'start_time' => '20:00',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $monthly = $response->json('data.regular_schedule.adoration.monthly_nth');
    expect($monthly)->toHaveCount(2);

    // Check labels are correct
    $labels = collect($monthly)->pluck('label')->all();
    expect($labels)->toContain('First Thursday');
    expect($labels)->toContain('Last Friday');
});

test('it returns schedule details including time and language', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->sunday()->create([
        'start_time' => '10:00',
        'end_time' => '11:00',
        'language' => 'English',
        'activity_type_description' => 'traditional-latin-mass',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $sundayMass = $response->json('data.regular_schedule.mass.sunday.0');
    expect($sundayMass['start_time'])->toBe('10:00');
    expect($sundayMass['end_time'])->toBe('11:00');
    expect($sundayMass['language'])->toBe('English');
    expect($sundayMass['activity_type_description'])->toBe('traditional-latin-mass');
    expect($sundayMass['day_pattern'])->toBe(DayPattern::Sunday->value);
});

test('it includes one-time activities with their specific dates', function () {
    $church = Church::factory()->create();

    $eventDate = Carbon::today()->addDays(14);

    OneTimeActivity::factory()->recycle($church)->create([
        'date' => $eventDate,
        'start_time' => '15:00',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $oneTime = $response->json('data.one_time_activities.mass');
    expect($oneTime)->toHaveCount(1);
    expect($oneTime[0]['date'])->toBe($eventDate->toDateString());
});

test('it excludes past one-time activities and includes current or future ones', function () {
    $church = Church::factory()->create();

    // Past one-time event (should be excluded)
    OneTimeActivity::factory()->recycle($church)->create([
        'date' => Carbon::yesterday(),
        'start_time' => '10:00',
    ]);

    // Today's one-time event (should be included)
    $todayActivity = OneTimeActivity::factory()->recycle($church)->create([
        'date' => Carbon::today(),
        'start_time' => '11:00',
    ]);

    // Future one-time event (should be included)
    $futureActivity = OneTimeActivity::factory()->recycle($church)->create([
        'date' => Carbon::today()->addDays(7),
        'start_time' => '12:00',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $oneTime = $response->json('data.one_time_activities.mass');
    expect($oneTime)->toHaveCount(2);

    $returnedIds = collect($oneTime)->pluck('id')->all();
    expect($returnedIds)->toContain($todayActivity->id);
    expect($returnedIds)->toContain($futureActivity->id);
});

test('it returns one-time activities grouped by type', function () {
    $church = Church::factory()->create();

    $futureDate = Carbon::today()->addDays(7);

    OneTimeActivity::factory()->recycle($church)->ofType('mass')->create(['date' => $futureDate]);
    OneTimeActivity::factory()->recycle($church)->ofType('confession')->create(['date' => $futureDate]);
    OneTimeActivity::factory()->recycle($church)->ofType('adoration')->create(['date' => $futureDate]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $response->assertJsonStructure([
        'data' => [
            'one_time_activities' => [
                'mass',
                'confession',
                'adoration',
            ],
        ],
    ]);

    expect($response->json('data.one_time_activities.mass'))->toHaveCount(1);
    expect($response->json('data.one_time_activities.confession'))->toHaveCount(1);
    expect($response->json('data.one_time_activities.adoration'))->toHaveCount(1);
});

test('it returns liturgical schedules with holiday information', function () {
    $church = Church::factory()->create();
    $holiday = Holiday::factory()->create(['name' => 'Christmas']);

    $liturgicalSchedule = LiturgicalSchedule::factory()->recycle($church)->create([
        'holiday_id' => $holiday->id,
        'replaces_regular_schedule' => true,
        'notes' => 'Special Christmas schedule',
    ]);

    LiturgicalScheduleTime::factory()->create([
        'liturgical_schedule_id' => $liturgicalSchedule->id,
        'type' => 'mass',
        'start_time' => '00:00',
        'language' => 'English',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $liturgicalSchedules = $response->json('data.liturgical_schedules');
    expect($liturgicalSchedules)->toHaveCount(1);
    expect($liturgicalSchedules[0]['holiday']['name'])->toBe('Christmas');
    expect($liturgicalSchedules[0]['replaces_regular_schedule'])->toBeTrue();
    expect($liturgicalSchedules[0]['notes'])->toBe('Special Christmas schedule');
    expect($liturgicalSchedules[0]['times'])->toHaveCount(1);
});

test('it returns specific day schedules with days_of_week array', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->specificDays(['Monday', 'Wednesday', 'Friday'])->create([
        'start_time' => '06:30',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $specific = $response->json('data.regular_schedule.mass.specific');
    expect($specific)->toHaveCount(1);
    expect($specific[0]['day_pattern'])->toBe(DayPattern::Specific->value);
    expect($specific[0]['days_of_week'])->toBe(['Monday', 'Wednesday', 'Friday']);
});

test('it returns weekday schedules correctly', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->weekdays()->create([
        'start_time' => '07:00',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $weekdays = $response->json('data.regular_schedule.mass.weekdays');
    expect($weekdays)->toHaveCount(1);
    expect($weekdays[0]['day_pattern'])->toBe(DayPattern::Weekdays->value);
});

test('it returns saturday schedules correctly', function () {
    $church = Church::factory()->create();

    ActivitySchedule::factory()->recycle($church)->saturday()->create([
        'start_time' => '18:00',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $saturday = $response->json('data.regular_schedule.mass.saturday');
    expect($saturday)->toHaveCount(1);
    expect($saturday[0]['day_pattern'])->toBe(DayPattern::Saturday->value);
});

test('it includes day details when schedule has variations', function () {
    $church = Church::factory()->create();

    $schedule = ActivitySchedule::factory()->recycle($church)->weekdays()->withDayVariations()->create([
        'start_time' => '07:00',
        'language' => 'English',
    ]);

    // Create day detail for Monday with different time
    $schedule->dayDetails()->create([
        'day_of_week' => 'Monday',
        'start_time' => '06:30',
        'language' => 'English',
        'is_skipped' => false,
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $weekdays = $response->json('data.regular_schedule.mass.weekdays');
    expect($weekdays)->toHaveCount(1);
    expect($weekdays[0]['has_day_variations'])->toBeTrue();
    expect($weekdays[0]['day_details'])->toHaveCount(1);
    expect($weekdays[0]['day_details'][0]['day_of_week'])->toBe('Monday');
    expect($weekdays[0]['day_details'][0]['start_time'])->toBe('06:30');
});

test('it does not include legacy mass/confession/adoration keys', function () {
    $church = Church::factory()->create();
    ActivitySchedule::factory()->recycle($church)->create(['type' => 'mass']);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    // Legacy keys should NOT exist
    expect($response->json('data'))->not->toHaveKey('mass');
    expect($response->json('data'))->not->toHaveKey('confession');
    expect($response->json('data'))->not->toHaveKey('adoration');
});

test('it resolves church by code_name slug', function () {
    $church = Church::factory()->create(['name' => 'Christ the King', 'city' => 'Kampala']);

    getJson(route('church-details', ['church' => $church->code_name]))
        ->assertOk()
        ->assertJsonPath('data.details.id', $church->id);
});

test('it resolves church by numeric id', function () {
    $church = Church::factory()->create();

    getJson(route('church-details', ['church' => $church->id]))
        ->assertOk()
        ->assertJsonPath('data.details.id', $church->id);
});
