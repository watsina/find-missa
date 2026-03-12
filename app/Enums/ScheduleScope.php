<?php

namespace App\Enums;

use App\Models\Church;
use App\Models\Diocese;
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
            self::Church => Church::class,
            self::Diocese => Diocese::class,
        };
    }
}
