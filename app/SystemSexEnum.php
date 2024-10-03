<?php

namespace App;

use App\Traits\GetEnums;

enum SystemSexEnum: string
{
    use GetEnums;
    case MALE = 'male';
    case FEMALE = 'female';
}
