<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Holiday;
use App\Enums\HolidayType;
use App\Models\HolidayInstance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HolidayService
{
    /**
     * Cache TTL for Easter dates (1 year in seconds).
     */
    private const EASTER_CACHE_TTL = 31536000;

    /**
     * Get the date of Easter Sunday for a given year.
     * Uses the Anonymous Gregorian algorithm.
     */
    public function getEasterDate(int $year): Carbon
    {
        return Cache::remember(
            "easter_date_{$year}",
            self::EASTER_CACHE_TTL,
            fn () => $this->calculateEasterDate($year)
        );
    }

    /**
     * Calculate Easter Sunday using the Anonymous Gregorian algorithm.
     * This is the algorithm used by most Western Christian churches.
     *
     * @see https://en.wikipedia.org/wiki/Date_of_Easter#Anonymous_Gregorian_algorithm
     */
    protected function calculateEasterDate(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::createFromDate($year, $month, $day);
    }

    /**
     * Compute the date for a holiday in a specific year.
     */
    public function computeHolidayDate(Holiday $holiday, int $year): Carbon
    {
        return match ($holiday->type) {
            HolidayType::Fixed => $this->computeFixedDate($holiday, $year),
            HolidayType::EasterRelative => $this->computeEasterRelativeDate($holiday, $year),
            HolidayType::Computed => $this->computeFromRule($holiday, $year),
        };
    }

    /**
     * Compute a fixed date holiday.
     */
    protected function computeFixedDate(Holiday $holiday, int $year): Carbon
    {
        return Carbon::createFromDate($year, $holiday->fixed_month, $holiday->fixed_day);
    }

    /**
     * Compute an Easter-relative holiday date.
     */
    protected function computeEasterRelativeDate(Holiday $holiday, int $year): Carbon
    {
        $easter = $this->getEasterDate($year);

        return $easter->copy()->addDays($holiday->easter_offset_days);
    }

    /**
     * Compute a date based on a computation rule.
     *
     * Supports two rule formats:
     *
     * 1. Nth-day-of-month: {"nth": 4, "day": "thursday", "month": 11}
     *    Means "4th Thursday of November". Use {"last": true} for "last X of month".
     *
     * 2. Special rules: {"special": "sunday_in_octave_of_christmas"}
     *    Named rules for holidays with unique computation logic.
     *
     * @param  array{nth?: int, day?: string, month?: int, last?: bool, special?: string}|null  $rule
     */
    protected function computeFromRule(Holiday $holiday, int $year): Carbon
    {
        $rule = $holiday->computation_rule;

        if (! $rule) {
            throw new \InvalidArgumentException("Holiday '{$holiday->name}' has no valid computation rule");
        }

        // Special named rules for holidays with unique computation logic
        if (isset($rule['special'])) {
            return $this->computeSpecialRule($holiday, $rule['special'], $year);
        }

        if (! isset($rule['month'])) {
            throw new \InvalidArgumentException("Holiday '{$holiday->name}' has no valid computation rule");
        }

        $month = $rule['month'];
        $dayOfWeek = $rule['day'] ?? 'sunday';
        $nth = $rule['nth'] ?? 1;
        $last = $rule['last'] ?? false;

        if ($last) {
            return $this->getLastDayOfWeekInMonth($year, $month, $dayOfWeek);
        }

        return $this->getNthDayOfWeekInMonth($year, $month, $dayOfWeek, $nth);
    }

    /**
     * Compute a date for a special named rule.
     *
     * Special rules handle holidays whose date logic doesn't fit the standard
     * "nth day of week in month" pattern.
     */
    protected function computeSpecialRule(Holiday $holiday, string $ruleName, int $year): Carbon
    {
        return match ($ruleName) {
            'sunday_in_octave_of_christmas' => $this->computeSundayInOctaveOfChristmas($year),
            default => throw new \InvalidArgumentException("Unknown special computation rule '{$ruleName}' for holiday '{$holiday->name}'"),
        };
    }

    /**
     * Compute the Feast of the Holy Family — the Sunday within the Octave of Christmas.
     *
     * The Octave of Christmas spans Dec 25 through Jan 1. The Holy Family is celebrated
     * on the first Sunday that falls between Dec 26 and Dec 31. However, when Christmas
     * itself falls on a Sunday (making the next Sunday Jan 1 — the Solemnity of Mary),
     * the Holy Family is celebrated on Dec 30 instead.
     */
    protected function computeSundayInOctaveOfChristmas(int $year): Carbon
    {
        $christmas = Carbon::createFromDate($year, 12, 25);

        if ($christmas->isSunday()) {
            // When Christmas is Sunday, the next Sunday (Jan 1) is the Solemnity of Mary,
            // so Holy Family is celebrated on Friday, December 30.
            return Carbon::createFromDate($year, 12, 30);
        }

        // Find the first Sunday after Christmas within the Octave (Dec 26-31)
        $nextSunday = $christmas->copy()->next(Carbon::SUNDAY);

        // The next Sunday will always be within Dec 26-31 when Christmas is not Sunday
        return $nextSunday;
    }

    /**
     * Get the nth occurrence of a day of week in a month.
     * e.g., 4th Thursday of November.
     */
    protected function getNthDayOfWeekInMonth(int $year, int $month, string $dayOfWeek, int $nth): Carbon
    {
        $firstOfMonth = Carbon::createFromDate($year, $month, 1);
        $dayNumber = $this->getDayNumber($dayOfWeek);

        // Find the first occurrence of this day in the month
        $daysUntilFirst = ($dayNumber - $firstOfMonth->dayOfWeek + 7) % 7;
        $firstOccurrence = $firstOfMonth->copy()->addDays($daysUntilFirst);

        // Add weeks to get to the nth occurrence
        return $firstOccurrence->addWeeks($nth - 1);
    }

    /**
     * Get the last occurrence of a day of week in a month.
     * e.g., Last Monday of May.
     */
    protected function getLastDayOfWeekInMonth(int $year, int $month, string $dayOfWeek): Carbon
    {
        $lastOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        $dayNumber = $this->getDayNumber($dayOfWeek);

        // Find the last occurrence of this day in the month
        $daysBack = ($lastOfMonth->dayOfWeek - $dayNumber + 7) % 7;

        return $lastOfMonth->subDays($daysBack);
    }

    /**
     * Convert day name to Carbon day number (0 = Sunday, 6 = Saturday).
     */
    protected function getDayNumber(string $dayOfWeek): int
    {
        return match (strtolower($dayOfWeek)) {
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
            default => throw new \InvalidArgumentException("Invalid day of week: {$dayOfWeek}"),
        };
    }

    /**
     * Get or create a holiday instance for a specific year.
     * This caches the computed date in the database.
     */
    public function getOrCreateInstance(Holiday $holiday, int $year): HolidayInstance
    {
        $instance = $holiday->getInstanceForYear($year);

        if ($instance) {
            return $instance;
        }

        $date = $this->computeHolidayDate($holiday, $year);

        return HolidayInstance::create([
            'holiday_id' => $holiday->id,
            'year' => $year,
            'date' => $date->toDateString(),
        ]);
    }

    /**
     * Compute and cache instances for all holidays for a given year.
     *
     * @return Collection<int, HolidayInstance>
     */
    public function computeInstancesForYear(int $year, ?int $teamId = null): Collection
    {
        $holidays = Holiday::query()
            ->availableForTeam($teamId)
            ->get();

        $instances = collect();

        foreach ($holidays as $holiday) {
            $instances->push($this->getOrCreateInstance($holiday, $year));
        }

        return $instances;
    }

    /**
     * Get all holiday instances for a date range.
     *
     * @return Collection<int, HolidayInstance>
     */
    public function getInstancesInRange(string $startDate, string $endDate, ?int $teamId = null): Collection
    {
        $startYear = (int) Carbon::parse($startDate)->year;
        $endYear = (int) Carbon::parse($endDate)->year;

        // Ensure instances exist for all years in range
        for ($year = $startYear; $year <= $endYear; $year++) {
            $this->computeInstancesForYear($year, $teamId);
        }

        return HolidayInstance::query()
            ->whereHas('holiday', function ($query) use ($teamId) {
                $query->availableForTeam($teamId);
            })
            ->inDateRange($startDate, $endDate)
            ->with('holiday')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get holidays on a specific date that suppress regular schedules.
     *
     * Returns holiday instances where the parent holiday has
     * suppresses_regular_schedule = true, indicating that regular
     * activity schedules should not be shown on this date.
     *
     * @return Collection<int, HolidayInstance>
     */
    public function getSuppressingHolidaysOnDate(string $date, ?int $teamId = null): Collection
    {
        $year = (int) Carbon::parse($date)->year;

        // Ensure instances exist for this year
        $this->computeInstancesForYear($year, $teamId);

        return HolidayInstance::query()
            ->whereHas('holiday', function ($query) use ($teamId) {
                $query->availableForTeam($teamId)
                    ->suppressesRegularSchedule();
            })
            ->onDate($date)
            ->with('holiday')
            ->get();
    }

    /**
     * Check if a specific date is a holiday.
     */
    public function isHoliday(string $date, ?int $teamId = null): bool
    {
        $year = (int) Carbon::parse($date)->year;

        // Ensure instances exist for this year
        $this->computeInstancesForYear($year, $teamId);

        return HolidayInstance::query()
            ->whereHas('holiday', function ($query) use ($teamId) {
                $query->availableForTeam($teamId);
            })
            ->onDate($date)
            ->exists();
    }

    /**
     * Get holidays on a specific date.
     *
     * @return Collection<int, HolidayInstance>
     */
    public function getHolidaysOnDate(string $date, ?int $teamId = null): Collection
    {
        $year = (int) Carbon::parse($date)->year;

        // Ensure instances exist for this year
        $this->computeInstancesForYear($year, $teamId);

        return HolidayInstance::query()
            ->whereHas('holiday', function ($query) use ($teamId) {
                $query->availableForTeam($teamId);
            })
            ->onDate($date)
            ->with('holiday')
            ->get();
    }

    /**
     * Check if a date is a Holy Day of Obligation.
     */
    public function isHolyDayOfObligation(string $date, ?int $teamId = null): bool
    {
        $year = (int) Carbon::parse($date)->year;

        // Ensure instances exist for this year
        $this->computeInstancesForYear($year, $teamId);

        return HolidayInstance::query()
            ->whereHas('holiday', function ($query) use ($teamId) {
                $query->availableForTeam($teamId)
                    ->where('is_holy_day_of_obligation', true);
            })
            ->onDate($date)
            ->exists();
    }

    /**
     * Clear cached instances for a holiday.
     * Useful when a holiday definition is updated.
     */
    public function clearInstancesForHoliday(Holiday $holiday): void
    {
        $holiday->instances()->delete();
    }

    /**
     * Clear all cached instances for a year.
     */
    public function clearInstancesForYear(int $year): void
    {
        HolidayInstance::query()
            ->where('year', $year)
            ->delete();

        Cache::forget("easter_date_{$year}");
    }
}
