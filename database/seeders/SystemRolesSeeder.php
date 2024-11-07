<?php

namespace Svr\Core\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Created seeds for table system.system_roles
 */
class SystemRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system.system_roles')->truncate();

        DB::table('system.system_roles')->insert([
            [
                "role_name_long"     => "Администратор",
                "role_name_short"    => "Админ",
                "role_slug"          => "admin",
                "role_status"        => "enabled",
                "role_status_delete" => "active",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "role_name_long"     => "Ветеринарный врач хозяйства",
                "role_name_short"    => "Вет. врач хоз-ва",
                "role_slug"          => "doctor_company",
                "role_status"        => "enabled",
                "role_status_delete" => "active",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "role_name_long"     => "Ветеринарный врач района",
                "role_name_short"    => "Вет. врач района",
                "role_slug"          => "doctor_district",
                "role_status"        => "enabled",
                "role_status_delete" => "active",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "role_name_long"     => "Ветеринарный врач региона",
                "role_name_short"    => "Вет. врач региона",
                "role_slug"          => "doctor_region",
                "role_status"        => "enabled",
                "role_status_delete" => "active",
                "created_at"         => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"         => Carbon::now()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
