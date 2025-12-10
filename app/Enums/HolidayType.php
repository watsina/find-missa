<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum HolidayType: string
{
    use EnumToArray;

    case Fixed = 'fixed';
    case EasterRelative = 'easter_relative';
    case Computed = 'computed';

    /**
     * Get the display name for the holiday type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed Date',
            self::EasterRelative => 'Easter Relative',
            self::Computed => 'Computed',
        };
    }

    /**
     * Get description of how this type calculates dates.
     */
    public function description(): string
    {
        return match ($this) {
            self::Fixed => 'Same date every year (e.g., Christmas on December 25)',
            self::EasterRelative => 'Offset from Easter Sunday (e.g., Ash Wednesday is -46 days)',
            self::Computed => 'Computed using custom rules (e.g., 4th Thursday of November)',
        };
    }
}
