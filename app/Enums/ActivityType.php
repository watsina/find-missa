<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ActivityType: string
{
    use EnumToArray;

    case Mass = 'mass';
    case AnticipationMass = 'anticipation-mass';
    case TraditionalLatinMass = 'traditional-latin-mass';
    case Confession = 'confession';
    case Adoration = 'adoration';
    case Benediction = 'benediction';
}
