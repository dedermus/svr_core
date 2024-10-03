<?php

namespace Svr\Core\Enums;

use Svr\Core\Traits\GetEnums;

enum SystemBreedingValueEnum: string
{
    use GetEnums;
    case UNDEFINED = 'UNDEFINED';
    case BREEDING = 'BREEDING';
    case NON_BREEDING = 'NON_BREEDING';
}
