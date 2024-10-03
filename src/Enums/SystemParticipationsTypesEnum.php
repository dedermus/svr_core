<?php

namespace Svr\Core\Enums;

use Svr\Core\Traits\GetEnums;

enum SystemParticipationsTypesEnum: string
{
    use GetEnums;
    case COMPANY = 'company';
    case REGION = 'region';
    case DISTRICT = 'district';
    case ADMIN = 'admin';
}
