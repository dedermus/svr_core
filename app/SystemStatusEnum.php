<?php

namespace App;

use App\Traits\GetEnums;

enum SystemStatusEnum: string
{
    use GetEnums;
    case ENABLED = 'enabled';
    case DISABLED = 'disabled';
}
