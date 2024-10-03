<?php

namespace App;

use App\Traits\GetEnums;

enum ApplicationAnimalStatusEnum: string
{
    use GetEnums;
    case ADDED = 'added';
    case IN_APPLICATION = 'in_application';
    case SENT = 'sent';
    case REGISTERED = 'registered';
    case REJECTED = 'rejected';
}