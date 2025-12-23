<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ActivityOccurrence: string
{
    use EnumToArray;

    case Weekly = 'weekly';
    case Daily = 'daily';
    case OneTime = 'one-time';
    case MonthlyNthWeekday = 'monthly-nth-weekday';
    // Note: BiWeekly can be added here in the future when needed
    // case BiWeekly = 'bi-weekly';

    /**
     * Get a human-readable label for the occurrence type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Weekly',
            self::Daily => 'Daily',
            self::OneTime => 'One Time',
            self::MonthlyNthWeekday => 'Monthly (Specific Week)',
        };
    }

    /**
     * Check if this occurrence requires a day of week.
     */
    public function requiresDayOfWeek(): bool
    {
        return match ($this) {
            self::Weekly, self::MonthlyNthWeekday => true,
            default => false,
        };
    }

    /**
     * Check if this occurrence requires a week of month.
     */
    public function requiresWeekOfMonth(): bool
    {
        return $this === self::MonthlyNthWeekday;
    }
}
