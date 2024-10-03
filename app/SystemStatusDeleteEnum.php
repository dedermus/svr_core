<?php

namespace App;

use App\Traits\GetEnums;

enum SystemStatusDeleteEnum: string
{
    use GetEnums;
    case ACTIVE = 'active';
    case DELETED = 'deleted';
}
