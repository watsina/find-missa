<?php

namespace App\Enums;

use App\Concerns\EnumToArray;

enum DioceseType: string
{
    use EnumToArray;

    case DIOCESE = 'diocese';
    case ARCH_DIOCESE = 'archdiocese';
}
