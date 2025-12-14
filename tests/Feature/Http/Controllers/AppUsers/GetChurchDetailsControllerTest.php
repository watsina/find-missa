<?php

namespace Tests\Feature\Http\Controllers\AppUsers;

use App\Models\Church;
use App\Models\ActivityTime;
use App\Models\SchedulePeriod;
use Illuminate\Support\Carbon;
use App\Enums\ActivityOccurrence;

use function Pest\Laravel\getJson;

test('it shows the church details with the activities grouped.', function () {
    $church = Church::factory()->create();
    ActivityTime::factory(3)->recycle($church)->create(['type' => 'mass']);
    ActivityTime::factory(2)->recycle($church)->create(['type' => 'adoration']);
    ActivityTime::factory(1)->recycle($church)->create(['type' => 'confession']);

    getJson(route('church-details', ['church' => $church->id]))
        ->assertOk()
        ->assertJsonPath('data.details.id', $church->id)
        ->assertJsonCount(3, 'data.mass')
        ->assertJsonCount(2, 'data.adoration')
        ->assertJsonCount(1, 'data.confession');
});

test('it returns regular schedule grouped by occurrence type', function () {
    $church = Church::factory()->create();

    // Create weekly mass
    ActivityTime::factory()->recycle($church)->create([
        'type' => 'mass',
        'occurrence' => ActivityOccurrence::Weekly,
        'day_of_week' => 'Sunday',
        'start_time' => '10:00',
    ]);

    // Create daily mass
    ActivityTime::factory()->recycle($church)->create([
        'type' => 'mass',
        'occurrence' => ActivityOccurrence::Daily,
        'start_time' => '07:00',
    ]);

    // Create monthly mass
    ActivityTime::factory()->recycle($church)->create([
        'type' => 'mass',
        'occurrence' => ActivityOccurrence::MonthlyNthWeekday,
        'day_of_week' => 'Friday',
        'week_of_month' => 1,
        'start_time' => '18:00',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    // Check regular schedule structure
    $response->assertJsonStructure([
        'data' => [
            'regular_schedule' => [
                'mass' => [
                    'weekly',
                    'daily',
                    'monthly',
                    'one_time',
                ],
            ],
        ],
    ]);

    // Verify weekly activities are grouped by day
    $data = $response->json('data.regular_schedule.mass');
    expect($data['weekly'])->toHaveKey('Sunday');
    expect($data['weekly']['Sunday'])->toHaveCount(1);
    expect($data['daily'])->toHaveCount(1);
    expect($data['monthly'])->toHaveCount(1);
    expect($data['monthly'][0]['label'])->toBe('First Friday');
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
    ActivityTime::factory()->recycle($church)->create([
        'type' => 'mass',
        'occurrence' => ActivityOccurrence::Weekly,
        'day_of_week' => 'Sunday',
        'schedule_period_id' => null,
    ]);

    // Create schedule period specific activity
    ActivityTime::factory()->recycle($church)->create([
        'type' => 'mass',
        'occurrence' => ActivityOccurrence::Weekly,
        'day_of_week' => 'Wednesday',
        'schedule_period_id' => $schedulePeriod->id,
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    // Verify regular schedule has Sunday mass
    $regularSchedule = $response->json('data.regular_schedule.mass.weekly');
    expect($regularSchedule)->toHaveKey('Sunday');
    expect($regularSchedule)->not->toHaveKey('Wednesday');

    // Verify schedule periods are returned
    $periods = $response->json('data.schedule_periods');
    expect($periods)->toHaveCount(1);
    expect($periods[0]['name'])->toBe('Lent');
    expect($periods[0]['is_current'])->toBeTrue();

    // Verify schedule period has Wednesday mass
    $periodMass = $periods[0]['activities']['mass']['weekly'];
    expect($periodMass)->toHaveKey('Wednesday');
});

test('it returns monthly-nth-weekday activities with correct labels', function () {
    $church = Church::factory()->create();

    // Create activities for different weeks of month
    ActivityTime::factory()->recycle($church)->create([
        'type' => 'adoration',
        'occurrence' => ActivityOccurrence::MonthlyNthWeekday,
        'day_of_week' => 'Thursday',
        'week_of_month' => 1,
        'start_time' => '19:00',
    ]);

    ActivityTime::factory()->recycle($church)->create([
        'type' => 'adoration',
        'occurrence' => ActivityOccurrence::MonthlyNthWeekday,
        'day_of_week' => 'Friday',
        'week_of_month' => 5, // Last
        'start_time' => '20:00',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $monthly = $response->json('data.regular_schedule.adoration.monthly');
    expect($monthly)->toHaveCount(2);

    // Check labels are correct
    $labels = collect($monthly)->pluck('label')->all();
    expect($labels)->toContain('First Thursday');
    expect($labels)->toContain('Last Friday');
});

test('it returns activity details including time and language', function () {
    $church = Church::factory()->create();

    ActivityTime::factory()->recycle($church)->create([
        'type' => 'mass',
        'occurrence' => ActivityOccurrence::Weekly,
        'day_of_week' => 'Sunday',
        'start_time' => '10:00',
        'end_time' => '11:00',
        'language' => 'English',
        'activity_type_description' => 'traditional-latin-mass',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $sundayMass = $response->json('data.regular_schedule.mass.weekly.Sunday.0');
    expect($sundayMass['start_time'])->toBe('10:00');
    expect($sundayMass['end_time'])->toBe('11:00');
    expect($sundayMass['language'])->toBe('English');
    expect($sundayMass['activity_type_description'])->toBe('traditional-latin-mass');
});

test('it includes one-time activities with their specific dates', function () {
    $church = Church::factory()->create();

    $eventDate = Carbon::today()->addDays(14);

    ActivityTime::factory()->recycle($church)->create([
        'type' => 'mass',
        'occurrence' => ActivityOccurrence::OneTime,
        'date' => $eventDate,
        'start_time' => '15:00',
    ]);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    $oneTime = $response->json('data.regular_schedule.mass.one_time');
    expect($oneTime)->toHaveCount(1);
    expect($oneTime[0]['date'])->toBe($eventDate->toDateString());
});

test('it maintains backward compatibility with legacy mass/confession/adoration keys', function () {
    $church = Church::factory()->create();
    ActivityTime::factory()->recycle($church)->create(['type' => 'mass']);
    ActivityTime::factory()->recycle($church)->create(['type' => 'confession']);
    ActivityTime::factory()->recycle($church)->create(['type' => 'adoration']);

    $response = getJson(route('church-details', ['church' => $church->id]))
        ->assertOk();

    // Legacy keys should still exist for backward compatibility
    $response->assertJsonStructure([
        'data' => [
            'mass',
            'confession',
            'adoration',
        ],
    ]);
});
