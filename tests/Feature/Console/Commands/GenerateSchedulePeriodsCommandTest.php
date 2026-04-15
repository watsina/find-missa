<?php

use App\Models\SchedulePeriod;

beforeEach(function () {
    // Clear schedule periods
    SchedulePeriod::query()->delete();
});

describe('schedule-periods:generate command', function () {
    it('generates all liturgical periods for current and next year by default', function () {
        $currentYear = (int) now()->year;

        $this->artisan('schedule-periods:generate')
            ->assertSuccessful()
            ->expectsOutputToContain("Generating schedule periods for current and next year: {$currentYear}, ".($currentYear + 1));

        // Should create 7 periods per year = 14 total
        expect(SchedulePeriod::count())->toBe(14);

        // Verify all period types exist for current year
        expect(SchedulePeriod::where('slug', "advent-{$currentYear}")->exists())->toBeTrue();
        expect(SchedulePeriod::where('slug', "christmas-season-{$currentYear}")->exists())->toBeTrue();
        expect(SchedulePeriod::where('slug', "lent-{$currentYear}")->exists())->toBeTrue();
        expect(SchedulePeriod::where('slug', "holy-week-{$currentYear}")->exists())->toBeTrue();
        expect(SchedulePeriod::where('slug', "easter-season-{$currentYear}")->exists())->toBeTrue();
        expect(SchedulePeriod::where('slug', "ordinary-time-i-{$currentYear}")->exists())->toBeTrue();
        expect(SchedulePeriod::where('slug', "ordinary-time-ii-{$currentYear}")->exists())->toBeTrue();
    });

    it('generates periods for a specific year when --year option is provided', function () {
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful()
            ->expectsOutputToContain('Generating schedule periods for year: 2030');

        expect(SchedulePeriod::count())->toBe(7);
        expect(SchedulePeriod::where('slug', 'advent-2030')->exists())->toBeTrue();
        expect(SchedulePeriod::where('slug', 'lent-2030')->exists())->toBeTrue();
    });

    it('skips existing periods without --force option', function () {
        // Pre-create a period
        SchedulePeriod::create([
            'name' => 'Advent 2030',
            'slug' => 'advent-2030',
            'description' => 'Existing period',
            'start_date' => '2030-12-01',
            'end_date' => '2030-12-24',
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ]);

        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful()
            ->expectsOutputToContain('Skipped: Advent 2030');

        // Original description should be preserved
        $advent = SchedulePeriod::where('slug', 'advent-2030')->first();
        expect($advent->description)->toBe('Existing period');
    });

    it('updates existing periods when --force option is used', function () {
        // Pre-create a period with old description
        SchedulePeriod::create([
            'name' => 'Advent 2030',
            'slug' => 'advent-2030',
            'description' => 'Old description',
            'start_date' => '2030-12-01',
            'end_date' => '2030-12-24',
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ]);

        $this->artisan('schedule-periods:generate', ['--year' => 2030, '--force' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('Updated: Advent 2030');

        // Description should be updated
        $advent = SchedulePeriod::where('slug', 'advent-2030')->first();
        expect($advent->description)->toContain('season of preparation for Christmas');
    });

    it('creates periods with correct is_recurring flag', function () {
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful();

        $periods = SchedulePeriod::where('slug', 'like', '%-2030')->get();

        foreach ($periods as $period) {
            expect($period->is_recurring)->toBeTrue();
        }
    });

    it('creates universal periods with null team_id', function () {
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful();

        $periods = SchedulePeriod::where('slug', 'like', '%-2030')->get();

        foreach ($periods as $period) {
            expect($period->team_id)->toBeNull();
        }
    });
});

describe('liturgical period date calculations', function () {
    it('calculates Lent dates correctly relative to Easter', function () {
        // Easter 2030 is April 21
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful();

        $lent = SchedulePeriod::where('slug', 'lent-2030')->first();

        // Ash Wednesday is 46 days before Easter
        expect($lent->start_date->format('Y-m-d'))->toBe('2030-03-06'); // March 6, 2030
        // Holy Thursday is 3 days before Easter
        expect($lent->end_date->format('Y-m-d'))->toBe('2030-04-18'); // April 18, 2030
    });

    it('calculates Easter season dates correctly', function () {
        // Easter 2030 is April 21
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful();

        $easter = SchedulePeriod::where('slug', 'easter-season-2030')->first();

        // Easter Sunday
        expect($easter->start_date->format('Y-m-d'))->toBe('2030-04-21');
        // Pentecost is 49 days after Easter
        expect($easter->end_date->format('Y-m-d'))->toBe('2030-06-09'); // June 9, 2030
    });

    it('calculates Holy Week dates correctly', function () {
        // Easter 2030 is April 21
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful();

        $holyWeek = SchedulePeriod::where('slug', 'holy-week-2030')->first();

        // Palm Sunday is 7 days before Easter
        expect($holyWeek->start_date->format('Y-m-d'))->toBe('2030-04-14');
        // Holy Saturday is 1 day before Easter
        expect($holyWeek->end_date->format('Y-m-d'))->toBe('2030-04-20');
    });

    it('calculates Advent dates correctly', function () {
        // Christmas 2030 is on Wednesday
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful();

        $advent = SchedulePeriod::where('slug', 'advent-2030')->first();

        // Advent starts 4 Sundays before Christmas
        expect($advent->start_date->isSunday())->toBeTrue();
        expect($advent->end_date->format('Y-m-d'))->toBe('2030-12-24');
    });

    it('calculates Christmas season spanning two years', function () {
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful();

        $christmas = SchedulePeriod::where('slug', 'christmas-season-2030')->first();

        expect($christmas->start_date->format('Y-m-d'))->toBe('2030-12-25');
        // Ends on Baptism of the Lord (Sunday after Jan 6)
        expect($christmas->end_date->year)->toBe(2031);
        expect($christmas->end_date->isSunday())->toBeTrue();
    });

    it('calculates Ordinary Time I correctly', function () {
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful();

        $ordinaryI = SchedulePeriod::where('slug', 'ordinary-time-i-2030')->first();
        $lent = SchedulePeriod::where('slug', 'lent-2030')->first();

        // Ends day before Ash Wednesday (Lent start)
        expect($ordinaryI->end_date->addDay()->format('Y-m-d'))->toBe($lent->start_date->format('Y-m-d'));
    });

    it('calculates Ordinary Time II correctly', function () {
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful();

        $ordinaryII = SchedulePeriod::where('slug', 'ordinary-time-ii-2030')->first();
        $easter = SchedulePeriod::where('slug', 'easter-season-2030')->first();
        $advent = SchedulePeriod::where('slug', 'advent-2030')->first();

        // Starts day after Pentecost (Easter season end)
        expect($ordinaryII->start_date->subDay()->format('Y-m-d'))->toBe($easter->end_date->format('Y-m-d'));
        // Ends day before Advent
        expect($ordinaryII->end_date->addDay()->format('Y-m-d'))->toBe($advent->start_date->format('Y-m-d'));
    });
});

describe('command output', function () {
    it('displays summary with correct counts', function () {
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful()
            ->expectsOutputToContain('7 created, 0 updated, 0 skipped');
    });

    it('displays created periods in output', function () {
        $this->artisan('schedule-periods:generate', ['--year' => 2030])
            ->assertSuccessful()
            ->expectsOutputToContain('+ Created: Advent 2030')
            ->expectsOutputToContain('+ Created: Lent 2030');
    });
});
