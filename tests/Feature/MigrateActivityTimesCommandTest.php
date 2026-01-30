<?php

use App\Models\Church;
use App\Models\ActivityTag;
use App\Models\ActivityTime;
use App\Models\OneTimeActivity;
use App\Models\ActivitySchedule;
use App\Models\ScheduleOverride;
use App\Enums\ActivityOccurrence;

use function Pest\Laravel\artisan;

use App\Models\ActivityTimeException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('summarizes actions for dry run without writing data', function () {
    $church = Church::factory()->create();
    $tag = ActivityTag::factory()->create();

    $oneTime = ActivityTime::factory()->create([
        'church_id' => $church->id,
        'occurrence' => ActivityOccurrence::OneTime,
        'date' => now()->addDays(2),
        'start_time' => '09:00:00',
    ]);
    $oneTime->tags()->attach($tag);

    $daily = ActivityTime::factory()->create([
        'church_id' => $church->id,
        'occurrence' => ActivityOccurrence::Daily,
        'start_time' => '08:00:00',
    ]);
    $daily->tags()->attach($tag);

    $weekly = ActivityTime::factory()->create([
        'church_id' => $church->id,
        'occurrence' => ActivityOccurrence::Weekly,
        'day_of_week' => 'Monday',
        'start_time' => '07:00:00',
    ]);
    $weekly->tags()->attach($tag);

    ActivityTimeException::factory()->create([
        'activity_time_id' => $weekly->id,
        'start_date' => '2025-12-25',
        'end_date' => '2025-12-25',
        'reason' => 'Christmas',
    ]);

    ActivitySchedule::factory()->create(['church_id' => $church->id]);
    OneTimeActivity::factory()->create(['church_id' => $church->id]);
    ScheduleOverride::factory()->forChurch($church)->create();

    artisan('activity-times:migrate', [
        '--church' => $church->id,
        '--dry-run' => true,
        '--force' => true,
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('Dry run summary:')
        ->expectsOutputToContain('ActivitySchedule')
        ->expectsOutputToContain('OneTimeActivity')
        ->expectsOutputToContain('ScheduleOverride')
        ->expectsOutputToContain('ActivityTag pivot')
        ->expectsOutputToContain('Per-church summary')
        ->expectsOutputToContain('Dry run complete. No changes were written.');

    expect(ActivitySchedule::count())->toBe(1)
        ->and(OneTimeActivity::count())->toBe(1)
        ->and(ScheduleOverride::count())->toBe(1);
});
