<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ActivityTagCategory: string
{
    use EnumToArray;

    case TimeOfDay = 'time_of_day';
    case DayType = 'day_type';
    case Audience = 'audience';
    case Custom = 'custom';

    /**
     * Get the display name for the category.
     */
    public function label(): string
    {
        return match ($this) {
            self::TimeOfDay => 'Time of Day',
            self::DayType => 'Day Type',
            self::Audience => 'Audience',
            self::Custom => 'Custom',
        };
    }
}
