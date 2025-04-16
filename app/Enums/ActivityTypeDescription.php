<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ActivityTypeDescription: string
{
    use EnumToArray;

    case Mass = 'mass';
    case GoodFridayService = 'good-friday-service';
    case EasterVigilMass = 'easter-vigil-mass';
    case EasterMass = 'easter-mass';
    case EasterMondayMass = 'easter-monday-mass';
    case AnticipationMass = 'anticipation-mass';
    case TraditionalLatinMass = 'traditional-latin-mass';
    case Confession = 'confession';
    case Adoration = 'adoration';
    case Benediction = 'benediction';
}
