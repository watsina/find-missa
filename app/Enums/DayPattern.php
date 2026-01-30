<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum DayPattern: string
{
    use EnumToArray;

    case Weekdays = 'weekdays';
    case Saturday = 'saturday';
    case Sunday = 'sunday';
    case Daily = 'daily';
    case Specific = 'specific';
    case MonthlyNth = 'monthly-nth';

    /**
     * Get a human-readable label for the day pattern.
     */
    public function label(): string
    {
        return match ($this) {
            self::Weekdays => 'Weekdays (Mon-Fri)',
            self::Saturday => 'Saturday',
            self::Sunday => 'Sunday',
            self::Daily => 'Daily',
            self::Specific => 'Specific Days',
            self::MonthlyNth => 'Monthly (Specific Week)',
        };
    }

    /**
     * Get the days of week that this pattern covers.
     *
     * @return array<string>
     */
    public function getDays(): array
    {
        return match ($this) {
            self::Weekdays => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            self::Saturday => ['Saturday'],
            self::Sunday => ['Sunday'],
            self::Daily => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            self::Specific, self::MonthlyNth => [],
        };
    }

    /**
     * Check if this pattern requires days_of_week JSON field.
     */
    public function requiresDaysOfWeek(): bool
    {
        return $this === self::Specific;
    }

    /**
     * Check if this pattern requires week_of_month and day_of_week fields.
     */
    public function requiresMonthlyFields(): bool
    {
        return $this === self::MonthlyNth;
    }

    /**
     * Check if a given day name matches this pattern.
     */
    public function matchesDay(string $dayName): bool
    {
        $normalizedDay = ucfirst(strtolower($dayName));

        return match ($this) {
            self::Daily => true,
            self::Weekdays => in_array($normalizedDay, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
            self::Saturday => $normalizedDay === 'Saturday',
            self::Sunday => $normalizedDay === 'Sunday',
            self::Specific, self::MonthlyNth => false,
        };
    }
}
