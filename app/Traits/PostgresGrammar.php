<?php

namespace App\Traits;

use Illuminate\Database\Schema\Grammars\Grammar;

trait PostgresGrammar
{
    public function enumExists(): void
    {
        Grammar::macro('typeSystem.system_status', function () {
            return 'system.system_status';
        });
        Grammar::macro('typeSystem.system_status_delete', function () {
            return 'system.system_status_delete';
        });
        Grammar::macro('typeSystem.system_status_confirm', function () {
            return 'system.system_status_confirm';
        });
        Grammar::macro('typeSystem.system_status_notification', function () {
            return 'system.system_status_notification';
        });
        Grammar::macro('typeSystem.system_sex', function () {
            return 'system.system_sex';
        });
        Grammar::macro('typeSystem.system_breeding_value', function () {
            return 'system.system_breeding_value';
        });
        Grammar::macro('typeSystem.system_notifications_types', function () {
            return 'system.system_notifications_types';
        });
        Grammar::macro('typeSystem.application_status', function () {
            return 'system.application_status';
        });
        Grammar::macro('typeSystem.application_animal_status', function () {
            return 'system.application_animal_status';
        });
        Grammar::macro('typeSystem.import_status', function () {
            return 'system.import_status';
        });
        Grammar::macro('typeSystem.system_participations_types', function () {
            return 'system.system_participations_types';
        });
        Grammar::macro('typeSystem.herriot_error_types', function () {
            return 'system.herriot_error_types';
        });
    }
}
