<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\SchedulePeriod;
use Illuminate\Database\Seeder;
use App\Services\HolidayService;

class SchedulePeriodSeeder extends Seeder
{
    public function __construct(protected HolidayService $holidayService) {}

    /**
     * Run the database seeds.
     *
     * Seeds liturgical schedule periods for the current and next liturgical year.
     */
    public function run(): void
    {
        $currentYear = (int) now()->year;

        // Seed periods for current year and next year
        $this->seedPeriodsForYear($currentYear);
        $this->seedPeriodsForYear($currentYear + 1);
    }

    /**
     * Seed all liturgical schedule periods for a given year.
     */
    public function seedPeriodsForYear(int $year): void
    {
        $periods = $this->getLiturgicalPeriodsForYear($year);

        foreach ($periods as $period) {
            SchedulePeriod::updateOrCreate(
                ['slug' => $period['slug']],
                $period
            );
        }
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
     * Advent begins on the fourth Sunday before Christmas Day.
     *
     * @return array<string, mixed>
     */
    protected function getAdventPeriod(int $year): array
    {
        $christmas = Carbon::createFromDate($year, 12, 25);
        // Find the 4th Sunday before Christmas
        // Christmas can fall on any day, so we find the Sunday closest to Nov 30
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
     * The Christmas season ends on the Sunday after Epiphany (Jan 6),
     * which is the Baptism of the Lord.
     *
     * @return array<string, mixed>
     */
    protected function getChristmasPeriod(int $year): array
    {
        $christmas = Carbon::createFromDate($year, 12, 25);
        // Baptism of the Lord is the Sunday after January 6
        $epiphany = Carbon::createFromDate($year + 1, 1, 6);
        $baptismOfTheLord = $epiphany->copy();

        if ($epiphany->dayOfWeek === Carbon::SUNDAY) {
            // If Epiphany is Sunday, Baptism is the following Monday (in some calendars)
            // But typically, if Jan 6 is Sunday, Baptism is Jan 13
            $baptismOfTheLord = $epiphany->copy()->next(Carbon::SUNDAY);
        } else {
            // Otherwise, it's the next Sunday after Epiphany
            $baptismOfTheLord = $epiphany->copy()->next(Carbon::SUNDAY);
        }

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
     * Lent is 40 days of fasting (excluding Sundays) before Easter.
     * Ash Wednesday is 46 days before Easter.
     * Lent ends at sundown on Holy Thursday (3 days before Easter).
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
     * Get Holy Week / Easter Triduum period.
     *
     * The sacred three days: Holy Thursday evening through Easter Sunday evening.
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
     * The Easter season is 50 days long, from Easter Sunday to Pentecost Sunday.
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
        // Starts day after Baptism of the Lord
        $epiphany = Carbon::createFromDate($year, 1, 6);
        $baptismOfTheLord = $epiphany->dayOfWeek === Carbon::SUNDAY
            ? $epiphany->copy()->next(Carbon::SUNDAY)
            : $epiphany->copy()->next(Carbon::SUNDAY);

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

        // Calculate start of Advent (4th Sunday before Christmas)
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
