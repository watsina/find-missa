<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ActivityType: string
{
    use EnumToArray;

    case Mass = 'mass';
    case Confession = 'confession';
    case Adoration = 'adoration';
}
