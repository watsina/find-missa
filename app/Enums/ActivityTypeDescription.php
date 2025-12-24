<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ActivityTypeDescription: string
{
    use EnumToArray;

    case Mass = 'mass';
    case Confession = 'confession';
    case Adoration = 'adoration';
    case Benediction = 'benediction';
    case TraditionalLatinMass = 'traditional-latin-mass';
    case AnticipationMass = 'anticipation-mass';
    case MassOfTheLordsSupper = 'mass-of-the-lords-supper';
    case GoodFridayService = 'good-friday-service';
    case EasterVigilMass = 'easter-vigil-mass';
    case EasterMass = 'easter-mass';
    case EasterMondayMass = 'easter-monday-mass';
    case ChristmasVigilMass = 'christmas-vigil-mass';
    case ChristmasMass = 'christmas-mass';
}
