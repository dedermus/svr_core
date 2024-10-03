<?php

namespace App;

use App\Traits\GetEnums;

enum ApplicationStatusEnum: string
{
    use GetEnums;
    case CREATED = 'created';
    case PREPARED = 'prepared';
    case SENT = 'sent';
    case COMPLETE_FULL = 'complete_full';
    case COMPLETE_PARTIAL = 'complete_partial';
    case FINISHED = 'finished';
}
