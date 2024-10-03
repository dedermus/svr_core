<?php

use App\ApplicationAnimalStatusEnum;
use App\ApplicationStatusEnum;
use App\HerriotErrorTypesEnum;
use App\ImportStatusEnum;
use App\SystemBreedingValueEnum;
use App\SystemNotificationsTypesEnum;
use App\SystemParticipationsTypesEnum;
use App\SystemSexEnum;
use App\SystemStatusConfirmEnum;
use App\SystemStatusDeleteEnum;
use App\SystemStatusEnum;
use App\SystemStatusNotificationEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS data');
        DB::statement("COMMENT ON SCHEMA data IS 'Основная схема'");

        DB::statement('CREATE SCHEMA IF NOT EXISTS directories');
        DB::statement("COMMENT ON SCHEMA data IS 'Схема справочников'");

        DB::statement('CREATE SCHEMA IF NOT EXISTS logs');
        DB::statement("COMMENT ON SCHEMA data IS 'Схема логов'");

        DB::statement('CREATE SCHEMA IF NOT EXISTS raw');
        DB::statement("COMMENT ON SCHEMA data IS 'Необработанные данные'");

        DB::statement('CREATE SCHEMA IF NOT EXISTS search');
        DB::statement("COMMENT ON SCHEMA data IS 'Схема для функций поиска'");

        DB::statement('CREATE SCHEMA IF NOT EXISTS service');
        DB::statement("COMMENT ON SCHEMA data IS 'Сервисная схема'");

        DB::statement('CREATE SCHEMA IF NOT EXISTS system');
        DB::statement("COMMENT ON SCHEMA data IS 'Системная схема'");

        DB::statement("CREATE TYPE system.system_status AS ENUM (".SystemStatusEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.system_status_delete AS ENUM (".SystemStatusDeleteEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.system_status_confirm AS ENUM (". SystemStatusConfirmEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.system_status_notification AS ENUM (". SystemStatusNotificationEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.system_sex AS ENUM (". SystemSexEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.system_breeding_value AS ENUM (". SystemBreedingValueEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.system_notifications_types AS ENUM (". SystemNotificationsTypesEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.application_status AS ENUM (". ApplicationStatusEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.application_animal_status AS ENUM (". ApplicationAnimalStatusEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.import_status AS ENUM (". ImportStatusEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.system_participations_types AS ENUM (". SystemParticipationsTypesEnum::get_value_str().")");
        DB::statement("CREATE TYPE system.herriot_error_types AS ENUM (". HerriotErrorTypesEnum::get_value_str().")");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP SCHEMA IF EXISTS data CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS directories CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS logs CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS search CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS service CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS system CASCADE');

        DB::statement('DROP TYPE IF EXISTS system.system_status');
        DB::statement('DROP TYPE IF EXISTS system.system_status_delete');
        DB::statement('DROP TYPE IF EXISTS system.system_status_confirm');
        DB::statement('DROP TYPE IF EXISTS system.system_status_notification');
        DB::statement('DROP TYPE IF EXISTS system.system_sex');
        DB::statement('DROP TYPE IF EXISTS system.system_breeding_value');
        DB::statement("DROP TYPE IF EXISTS system.system_notifications_types");
        DB::statement("DROP TYPE IF EXISTS system.application_status");
        DB::statement("DROP TYPE IF EXISTS system.application_animal_status");
        DB::statement("DROP TYPE IF EXISTS system.import_status");
        DB::statement("DROP TYPE IF EXISTS system.system_participations_types");
        DB::statement("DROP TYPE IF EXISTS system.herriot_error_types");

        DB::statement("DROP FUNCTION IF EXISTS system.update_timestamp()");
    }
};
