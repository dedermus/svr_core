<?php

namespace Svr\Core\Enums;

use Svr\Core\Traits\GetEnums;

enum SystemStatusDeleteEnum: string
{
    use GetEnums;
    case ACTIVE = 'active';
    case DELETED = 'deleted';
}
