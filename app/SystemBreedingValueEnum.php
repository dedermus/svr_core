<?php

namespace App;

use App\Traits\GetEnums;

enum SystemBreedingValueEnum: string
{
    use GetEnums;
    case UNDEFINED = 'UNDEFINED';
    case BREEDING = 'BREEDING';
    case NON_BREEDING = 'NON_BREEDING';
}
