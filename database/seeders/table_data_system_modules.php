<?php

namespace Svr\Core\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class table_data_system_modules extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system.system_modules')->truncate();

        DB::table('system.system_modules')->insert([
            [
                "module_name" => "Авторизация",
                "module_description" => "Авторизация внешних клиентов",
                "module_class_name" => "module_Auth",
                "module_slug" => "auth",
            ],
            [
                "module_name" => "Админы",
                "module_description" => "Управление  пользователями",
                "module_class_name" => "module_Users",
                "module_slug" => "users",
            ],
            [
                "module_name" => "Роли",
                "module_description" => "Управление  ролями",
                "module_class_name" => "module_Roles",
                "module_slug" => "roles",
            ],
            [
                "module_name" => "Хозяйство",
                "module_description" => "Хозяйство",
                "module_class_name" => "module_Companies",
                "module_slug" => "companies",
            ],
            [
                "module_name" => "Справочники",
                "module_description" => "Справочники",
                "module_class_name" => "module_Directories",
                "module_slug" => "directories",
            ],
            [
                "module_name" => "Уведомления",
                "module_description" => "Уведомления",
                "module_class_name" => "module_Notifications",
                "module_slug" => "notifications",
            ],
            [
                "module_name" => "Животные",
                "module_description" => "Животные",
                "module_class_name" => "module_Animals",
                "module_slug" => "animals",
            ],
            [
                "module_name" => "Заявки",
                "module_description" => "Заявки",
                "module_class_name" => "module_Applications",
                "module_slug" => "applications",
            ],
            [
                "module_name" => "Selex",
                "module_description" => "Интеграция с Selex",
                "module_class_name" => "module_Selex",
                "module_slug" => "selex",
            ],
            [
                "module_name" => "Дашборды",
                "module_description" => "Дашборды для администратора",
                "module_class_name" => "module_Dashboards",
                "module_slug" => "dashboards",
            ],
        ]);
    }
}
