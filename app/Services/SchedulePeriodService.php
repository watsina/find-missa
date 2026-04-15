<?php

namespace App\Services;

use Carbon\Carbon;

class SchedulePeriodService
{
    public function __construct(protected HolidayService $holidayService) {}

    /**
     * Get all liturgical period definitions for a specific year.
     *
     * Returns an array of period data arrays, each containing:
     * name, slug, description, start_date, end_date, is_active, is_recurring, team_id.
     *
     * Both GenerateSchedulePeriodsCommand and SchedulePeriodSeeder delegate to this
     * method to ensure a single source of truth for liturgical date calculations.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLiturgicalPeriodsForYear(int $year): array
    {
        $easter = $this->holidayService->getEasterDate($year);

        return [
            $this->getAdventPeriod($year),
            $this->getChristmasPeriod($year),
            $this->getLentPeriod($easter, $year),
            $this->getHolyWeekPeriod($easter, $year),
            $this->getEasterPeriod($easter, $year),
            $this->getOrdinaryTimeIPeriod($easter, $year),
            $this->getOrdinaryTimeIIPeriod($easter, $year),
        ];
    }

    /**
     * Get Advent period (4 Sundays before Christmas to Dec 24).
     *
     * Advent begins on the fourth Sunday before Christmas Day.
     * The nearest Sunday to November 30 (St. Andrew's Day) is always the first Sunday of Advent.
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
     * The Christmas season ends on the Baptism of the Lord, which is the
     * Sunday after Epiphany (January 6). The end date spans into the next
     * calendar year.
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
     * Lent is 40 days of fasting (excluding Sundays) before Easter.
     * Ash Wednesday is 46 calendar days before Easter Sunday.
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
     * Get Holy Week period (Palm Sunday to Holy Saturday).
     *
     * The most sacred week of the liturgical year. Palm Sunday is 7 days
     * before Easter, and Holy Saturday is the day before Easter Sunday.
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
     * The Easter season is 50 days long, from Easter Sunday to Pentecost Sunday
     * (49 days after Easter).
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
     * Get Ordinary Time I — after Christmas season until Ash Wednesday.
     *
     * The first segment of Ordinary Time in the liturgical year. Starts the day
     * after the Baptism of the Lord (end of Christmas season) and ends the day
     * before Ash Wednesday (start of Lent).
     *
     * @return array<string, mixed>
     */
    protected function getOrdinaryTimeIPeriod(Carbon $easter, int $year): array
    {
        $epiphany = Carbon::createFromDate($year, 1, 6);
        $baptismOfTheLord = $epiphany->copy()->next(Carbon::SUNDAY);
        $start = $baptismOfTheLord->copy()->addDay();
        $ashWednesday = $easter->copy()->subDays(46);
        $end = $ashWednesday->copy()->subDay();

        return [
            'name' => "Ordinary Time I {$year}",
            'slug' => "ordinary-time-i-{$year}",
            'description' => 'The period of Ordinary Time between the Christmas season and Lent.',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ];
    }

    /**
     * Get Ordinary Time II — after Pentecost until Advent.
     *
     * The second and longest segment of Ordinary Time. Starts the day after
     * Pentecost (end of Easter season) and ends the day before the first
     * Sunday of Advent.
     *
     * @return array<string, mixed>
     */
    protected function getOrdinaryTimeIIPeriod(Carbon $easter, int $year): array
    {
        $pentecost = $easter->copy()->addDays(49);
        $start = $pentecost->copy()->addDay();

        $christmas = Carbon::createFromDate($year, 12, 25);
        $advent1 = $christmas->copy()->subDays($christmas->dayOfWeek === Carbon::SUNDAY ? 21 : (21 + $christmas->dayOfWeek));
        $end = $advent1->copy()->subDay();

        return [
            'name' => "Ordinary Time II {$year}",
            'slug' => "ordinary-time-ii-{$year}",
            'description' => 'The longest period of Ordinary Time, from after Pentecost until the beginning of Advent.',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_active' => true,
            'is_recurring' => true,
            'team_id' => null,
        ];
    }
}
