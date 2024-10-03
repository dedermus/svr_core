<?php

namespace App;

use App\Traits\GetEnums;

enum SystemNotificationsTypesEnum: string
{
    use GetEnums;
    case INTEGRATION_SELEX_GOOD = 'integration_selex_good';
    case INTEGRATION_SELEX_BAD = 'integration_selex_bad';
    case INTEGRATION_SELEX_GUID_GOOD = 'integration_selex_guid_good';
    case INTEGRATION_SELEX_GUID_BAD = 'integration_selex_guid_bad';
    case INTEGRATION_HERRIOT_GOOD = 'integration_herriot_good';
    case INTEGRATION_HERRIOT_BAD = 'integration_herriot_bad';
    case USER_PASSWORD_CHANGE = 'user_password_change';
    case USER_CREATE = 'user_create';
    case USER_PASSWORD_RESTORE = 'user_password_restore';
    case APPLICATION_CREATED = 'application_created';
    case APPLICATION_PREPARED = 'application_prepared';
    case APPLICATION_SENT = 'application_sent';
    case APPLICATION_COMPLETE_FULL = 'application_complete_full';
    case APPLICATION_COMPLETE_PARTIAL = 'application_complete_partial';
    case APPLICATION_FINISHED = 'application_finished';
    case APPLICATION_ANIMAL_ADD = 'application_animal_add';
    case APPLICATION_ANIMAL_DELETE = 'application_animal_delete';
}
