<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum ActivityOccurrence: string
{
    use EnumToArray;

    case Weekly = 'weekly';
    case Daily = 'daily';
    case OneTime = 'one-time';
}
