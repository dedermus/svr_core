<?php

namespace Svr\Core\Enums;

use Svr\Core\Traits\GetEnums;

enum AnimalRegisterStatusEnum: string
{
    use GetEnums;
    case CREATED = 'created';
    case REGISTERED = 'registered';
    case CHECKED = 'checked';
    case ON_REGISTRATION = 'on_registration';
    case REJECTED = 'rejected';
    case ARCHIVED = 'archived';
}
