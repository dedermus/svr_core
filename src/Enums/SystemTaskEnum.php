<?php

namespace Svr\Core\Enums;

use Svr\Core\Traits\GetEnums;

enum SystemTaskEnum: int
{
    use GetEnums;
    case MILK = 1;
    case BEEF = 6;
    case SHEEP = 4;
}
