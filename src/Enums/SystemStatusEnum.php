<?php

namespace Svr\Core\Enums;

use Svr\Core\Traits\GetEnums;

enum SystemStatusEnum: string
{
    use GetEnums;
    case ENABLED = 'enabled';
    case DISABLED = 'disabled';
}
