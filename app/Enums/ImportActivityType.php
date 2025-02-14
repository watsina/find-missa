<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ImportActivityType: string
{
    use EnumToArray;

    case Mass = 'Mass';
    case AnticipationMass = 'Anticipation Mass';
    case TraditionalLatinMass = 'Traditional Latin Mass';
    case Confession = 'Confession';
    case Adoration = 'Adoration';
    case Benediction = 'Benediction';
}
