<?php

namespace App;

use App\Traits\GetEnums;
enum SystemStatusConfirmEnum: string
{
    use GetEnums;
    case NEW = 'new';
    case CHANGED = 'changed';
    case CONFIRMED = 'confirmed';
}
