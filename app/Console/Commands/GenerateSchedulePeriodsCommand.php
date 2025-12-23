<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\SchedulePeriod;
use Illuminate\Console\Command;
use App\Services\HolidayService;

class GenerateSchedulePeriodsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule-periods:generate
                            {--year= : Generate periods for a specific year (defaults to current and next year)}
                            {--force : Overwrite existing periods for the specified year(s)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate liturgical schedule periods for upcoming years';

    public function __construct(protected HolidayService $holidayService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $specificYear = $this->option('year');
        $force = $this->option('force');

        if ($specificYear) {
            $years = [(int) $specificYear];
            $this->info("Generating schedule periods for year: {$specificYear}");
        } else {
            $currentYear = (int) now()->year;
            $years = [$currentYear, $currentYear + 1];
            $this->info('Generating schedule periods for current and next year: '.implode(', ', $years));
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($years as $year) {
            $this->info("Processing year {$year}...");

            $periods = $this->getLiturgicalPeriodsForYear($year);

            foreach ($periods as $period) {
                $existing = SchedulePeriod::where('slug', $period['slug'])->first();

                if ($existing && ! $force) {
                    $skippedCount++;
                    $this->line("  - Skipped: {$period['name']} (already exists)");

                    continue;
                }

                if ($existing && $force) {
                    $existing->update($period);
                    $updatedCount++;
                    $this->line("  - Updated: {$period['name']}");
                } else {
                    SchedulePeriod::create($period);
                    $createdCount++;
                    $this->line("  + Created: {$period['name']}");
                }
            }
        }

        $this->newLine();
        $this->info("Summary: {$createdCount} created, {$updatedCount} updated, {$skippedCount} skipped.");

        return Command::SUCCESS;
    }

    /**
     * Get all liturgical periods for a specific year.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getLiturgicalPeriodsForYear(int $year): array
    {
        $easter = $this->holidayService->getEasterDate($year);

        return [
            $this->getAdventPeriod($year),
            $this->getChristmasPeriod($year),
            $this->getLentPeriod($easter, $year),
            $this->getHolyWeekPeriod($easter, $year),
            $this->getEasterPeriod($easter, $year),
            $this->getOrdinaryTimeWinterPeriod($easter, $year),
            $this->getOrdinaryTimeSummerPeriod($easter, $year),
        ];
    }

    /**
     * Get Advent period (4 Sundays before Christmas to Dec 24).
     *
     * @return array<string, mixed>
     */
    protected function getAdventPeriod(int $year): array
    {
        $christmas = Carbon::createFromDate($year, 12, 25);
        $advent1 = $christmas->copy()->subDays($christmas->dayOfWeek === Carbon::SUNDAY ? 21 : (21 + $christmas->dayOfWeek));

        return [
            'name' => "Advent {$year}",
            'slug' => "advent-{$year}",
            'description' => 'The season of preparation for Christmas, beginning four Sundays before Christmas Day.',
            'start_date' => $advent1->toDateString(),
            'end_date' => Carbon::createFromDate($year, 12, 24)->toDateString(),
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ];
    }

    /**
     * Get Christmas period (Dec 25 to Baptism of the Lord).
     *
     * @return array<string, mixed>
     */
    protected function getChristmasPeriod(int $year): array
    {
        $christmas = Carbon::createFromDate($year, 12, 25);
        $epiphany = Carbon::createFromDate($year + 1, 1, 6);
        $baptismOfTheLord = $epiphany->copy()->next(Carbon::SUNDAY);

        return [
            'name' => "Christmas Season {$year}-".($year + 1),
            'slug' => "christmas-season-{$year}",
            'description' => 'The season celebrating the birth of Christ, from Christmas Day to the Baptism of the Lord.',
            'start_date' => $christmas->toDateString(),
            'end_date' => $baptismOfTheLord->toDateString(),
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ];
    }

    /**
     * Get Lent period (Ash Wednesday to Holy Thursday).
     *
     * @return array<string, mixed>
     */
    protected function getLentPeriod(Carbon $easter, int $year): array
    {
        $ashWednesday = $easter->copy()->subDays(46);
        $holyThursday = $easter->copy()->subDays(3);

        return [
            'name' => "Lent {$year}",
            'slug' => "lent-{$year}",
            'description' => 'The 40-day season of penance and preparation for Easter, from Ash Wednesday to Holy Thursday.',
            'start_date' => $ashWednesday->toDateString(),
            'end_date' => $holyThursday->toDateString(),
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ];
    }

    /**
     * Get Holy Week period.
     *
     * @return array<string, mixed>
     */
    protected function getHolyWeekPeriod(Carbon $easter, int $year): array
    {
        $palmSunday = $easter->copy()->subDays(7);

        return [
            'name' => "Holy Week {$year}",
            'slug' => "holy-week-{$year}",
            'description' => 'The most sacred week of the liturgical year, from Palm Sunday through Holy Saturday.',
            'start_date' => $palmSunday->toDateString(),
            'end_date' => $easter->copy()->subDays(1)->toDateString(),
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ];
    }

    /**
     * Get Easter / Paschal period (Easter Sunday to Pentecost).
     *
     * @return array<string, mixed>
     */
    protected function getEasterPeriod(Carbon $easter, int $year): array
    {
        $pentecost = $easter->copy()->addDays(49);

        return [
            'name' => "Easter Season {$year}",
            'slug' => "easter-season-{$year}",
            'description' => 'The 50-day celebration of Christ\'s resurrection, from Easter Sunday to Pentecost.',
            'start_date' => $easter->toDateString(),
            'end_date' => $pentecost->toDateString(),
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ];
    }

    /**
     * Get Ordinary Time (Winter) - after Christmas season until Ash Wednesday.
     *
     * @return array<string, mixed>
     */
    protected function getOrdinaryTimeWinterPeriod(Carbon $easter, int $year): array
    {
        $epiphany = Carbon::createFromDate($year, 1, 6);
        $baptismOfTheLord = $epiphany->copy()->next(Carbon::SUNDAY);
        $start = $baptismOfTheLord->copy()->addDay();
        $ashWednesday = $easter->copy()->subDays(46);
        $end = $ashWednesday->copy()->subDay();

        return [
            'name' => "Ordinary Time (Winter) {$year}",
            'slug' => "ordinary-time-winter-{$year}",
            'description' => 'The period of Ordinary Time between the Christmas season and Lent.',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ];
    }

    /**
     * Get Ordinary Time (Summer/Fall) - after Pentecost until Advent.
     *
     * @return array<string, mixed>
     */
    protected function getOrdinaryTimeSummerPeriod(Carbon $easter, int $year): array
    {
        $pentecost = $easter->copy()->addDays(49);
        $start = $pentecost->copy()->addDay();

        $christmas = Carbon::createFromDate($year, 12, 25);
        $advent1 = $christmas->copy()->subDays($christmas->dayOfWeek === Carbon::SUNDAY ? 21 : (21 + $christmas->dayOfWeek));
        $end = $advent1->copy()->subDay();

        return [
            'name' => "Ordinary Time (Summer/Fall) {$year}",
            'slug' => "ordinary-time-summer-{$year}",
            'description' => 'The longest period of Ordinary Time, from after Pentecost until the beginning of Advent.',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ];
    }
}
