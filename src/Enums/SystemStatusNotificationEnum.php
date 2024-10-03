<?php

namespace Svr\Core\Enums;

use Svr\Core\Traits\GetEnums;

enum SystemStatusNotificationEnum: string
{
    use GetEnums;
    case EMAIL = 'email';
    case TELEGRAM = 'telegram';
}
