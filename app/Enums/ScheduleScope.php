<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ScheduleScope: string
{
    use EnumToArray;

    case Church = 'church';
    case Diocese = 'diocese';

    /**
     * Get a human-readable label for the scope.
     */
    public function label(): string
    {
        return match ($this) {
            self::Church => 'Church',
            self::Diocese => 'Diocese',
        };
    }

    /**
     * Get the model class for this scope type.
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::Church => \App\Models\Church::class,
            self::Diocese => \App\Models\Diocese::class,
        };
    }
}
