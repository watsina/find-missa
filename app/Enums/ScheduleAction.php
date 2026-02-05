<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ScheduleAction: string
{
    use EnumToArray;

    case Exclude = 'exclude';
    case Modify = 'modify';

    /**
     * Get a human-readable label for the action.
     */
    public function label(): string
    {
        return match ($this) {
            self::Exclude => 'Exclude',
            self::Modify => 'Modify',
        };
    }

    /**
     * Get a description of what this action does.
     */
    public function description(): string
    {
        return match ($this) {
            self::Exclude => 'Removes the matching activities from the schedule',
            self::Modify => 'Modifies the matching activities (e.g., change time)',
        };
    }

    /**
     * Check if this action requires replacement times.
     */
    public function requiresReplacementTimes(): bool
    {
        return $this === self::Modify;
    }
}
