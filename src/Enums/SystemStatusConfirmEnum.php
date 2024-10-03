<?php

namespace Svr\Core\Enums;

use Svr\Core\Traits\GetEnums;

enum SystemStatusConfirmEnum: string
{
    use GetEnums;
    case NEW = 'new';
    case CHANGED = 'changed';
    case CONFIRMED = 'confirmed';
}
