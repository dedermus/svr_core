<?php

namespace App;

use App\Traits\GetEnums;

enum SystemStatusNotificationEnum: string
{
    use GetEnums;
    case EMAIL = 'email';
    case TELEGRAM = 'telegram';
}
