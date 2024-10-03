<?php

namespace Svr\Core\Enums;

use Svr\Core\Traits\GetEnums;

enum SystemSexEnum: string
{
    use GetEnums;
    case MALE = 'male';
    case FEMALE = 'female';
}
