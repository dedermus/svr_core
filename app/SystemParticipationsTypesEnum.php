<?php

namespace App;

use App\Traits\GetEnums;

enum SystemParticipationsTypesEnum: string
{
    use GetEnums;
    case COMPANY = 'company';
    case REGION = 'region';
    case DISTRICT = 'district';
    case ADMIN = 'admin';
}
