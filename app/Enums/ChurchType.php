<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ChurchType: string
{
    use EnumToArray;

    case Parish = 'parish';
    case SubParish = 'sub-parish';
    case Basilica = 'basilica';
    case Minor_Basilica = 'minor-basilica';
}
