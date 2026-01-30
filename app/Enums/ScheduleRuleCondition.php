<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ScheduleRuleCondition: string
{
    use EnumToArray;

    case PublicHoliday = 'public_holiday';
    case LiturgicalDay = 'liturgical_day';

    /**
     * Get a human-readable label for the condition type.
     */
    public function label(): string
    {
        return match ($this) {
            self::PublicHoliday => 'Public Holiday',
            self::LiturgicalDay => 'Liturgical Day',
        };
    }

    /**
     * Get a description of what this condition checks for.
     */
    public function description(): string
    {
        return match ($this) {
            self::PublicHoliday => 'Triggers when the date is any public holiday',
            self::LiturgicalDay => 'Triggers when the date matches specific liturgical days',
        };
    }

    /**
     * Check if this condition requires the condition_holidays field.
     */
    public function requiresHolidays(): bool
    {
        return $this === self::LiturgicalDay;
    }
}
