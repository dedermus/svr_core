<?php

namespace Svr\Core\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Created seeds for table system.system_modules
 */
class SystemModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system.system_modules')->truncate();

        DB::table('system.system_modules')->insert([
            [
                "module_name"        => "Авторизация",
                "module_description" => "Авторизация внешних клиентов",
                "module_class_name"  => "module_Auth",
                "module_slug"        => "auth",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "module_name"        => "Админы",
                "module_description" => "Управление  пользователями",
                "module_class_name"  => "module_Users",
                "module_slug"        => "users",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "module_name"        => "Роли",
                "module_description" => "Управление  ролями",
                "module_class_name"  => "module_Roles",
                "module_slug"        => "roles",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "module_name"        => "Хозяйство",
                "module_description" => "Хозяйство",
                "module_class_name"  => "module_Companies",
                "module_slug"        => "companies",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "module_name"        => "Справочники",
                "module_description" => "Справочники",
                "module_class_name"  => "module_Directories",
                "module_slug"        => "directories",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "module_name"        => "Уведомления",
                "module_description" => "Уведомления",
                "module_class_name"  => "module_Notifications",
                "module_slug"        => "notifications",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "module_name"        => "Животные",
                "module_description" => "Животные",
                "module_class_name"  => "module_Animals",
                "module_slug"        => "animals",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "module_name"        => "Заявки",
                "module_description" => "Заявки",
                "module_class_name"  => "module_Applications",
                "module_slug"        => "applications",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "module_name"        => "Selex",
                "module_description" => "Интеграция с Selex",
                "module_class_name"  => "module_Selex",
                "module_slug"        => "selex",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "module_name"        => "Дашборды",
                "module_description" => "Дашборды для администратора",
                "module_class_name"  => "module_Dashboards",
                "module_slug"        => "dashboards",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
        ]);

        DB::statement("SELECT setval('system.system_modules_module_id_seq', (SELECT MAX(module_id) from system.system_modules))");
    }
}
