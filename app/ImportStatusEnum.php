<?php

namespace App;

use App\Traits\GetEnums;

enum ImportStatusEnum: string
{
    use GetEnums;
    case NEW = 'new';
    case IN_PROGRESS = 'in_progress';
    case ERROR = 'error';
    case COMPLETED = 'completed';
}
