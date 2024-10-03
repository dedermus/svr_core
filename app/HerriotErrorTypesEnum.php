<?php

namespace App;

use App\Traits\GetEnums;

enum HerriotErrorTypesEnum: string
{
    use GetEnums;
    case HTML = 'html';
    case XML = 'xml';
    case UNKNOWN = 'unknown';
    case GOOD = 'good';
}
