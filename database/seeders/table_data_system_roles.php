<?php

namespace Svr\Core\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class table_data_system_roles extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system.system_roles')->truncate();

        DB::table('system.system_roles')->insert([
            [
                "role_name_long" => "Администратор",
                "role_name_short" => "Админ",
                "role_slug" => "admin",
                "role_status" => "enabled",
                "role_status_delete" => "active",
            ],
            [
                "role_name_long" => "Ветеринарный врач хозяйства",
                "role_name_short" => "Вет. врач хоз-ва",
                "role_slug" => "doctor_company",
                "role_status" => "enabled",
                "role_status_delete" => "active",
            ],
            [
                "role_name_long" => "Ветеринарный врач района",
                "role_name_short" => "Вет. врач района",
                "role_slug" => "doctor_district",
                "role_status" => "enabled",
                "role_status_delete" => "active",
            ],
            [
                "role_name_long" => "Ветеринарный врач региона",
                "role_name_short" => "Вет. врач региона",
                "role_slug" => "doctor_region",
                "role_status" => "enabled",
                "role_status_delete" => "active",
            ]
        ]);
    }
}
