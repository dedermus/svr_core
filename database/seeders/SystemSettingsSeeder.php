<?php

namespace Svr\Core\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Created seeds for table system.system_settings
 */
class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system.system_settings')->truncate();

        DB::table('system.system_settings')->insert([
            [
                "owner_type"        => "system_mail",
                "owner_id"          => 0,
                "setting_code"      => "mail_username",
                "setting_value"     => "noreply@plinor.local",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "system_mail",
                "owner_id"          => 0,
                "setting_code"      => "mail_password",
                "setting_value"     => "WRJt2pNKdV!4WK678pfoQ",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "system_mail",
                "owner_id"          => 0,
                "setting_code"      => "mail_host",
                "setting_value"     => "mail.plinor.team",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "system_mail",
                "owner_id"          => 0,
                "setting_code"      => "mail_from",
                "setting_value"     => "noreply@plinor.ru",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "system_mail",
                "owner_id"          => 0,
                "setting_code"      => "mail_port",
                "setting_value"     => "587",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "system_mail",
                "owner_id"          => 0,
                "setting_code"      => "mail_charset",
                "setting_value"     => "utf-8",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "system_notifications",
                "owner_id"          => 0,
                "setting_code"      => "email_support",
                "setting_value"     => "tech@plinor.ru",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "herriot_api",
                "owner_id"          => 0,
                "setting_code"      => "herriot_issuer_id",
                "setting_value"     => "d637554e-d7ec-96c7-1f42-9e2f1226392b",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "telegram_informer_settings",
                "owner_id"          => 0,
                "setting_code"      => "apikey",
                "setting_value"     => "6853349389:AAGASo7GhUB5BLh4Xq75RZm1aNMYehWSNZ8",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "telegram_informer_users",
                "owner_id"          => 0,
                "setting_code"      => "user_id",
                "setting_value"     => "252033654",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "telegram_informer_users",
                "owner_id"          => 0,
                "setting_code"      => "user_id",
                "setting_value"     => "309797484",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "telegram_informer_users",
                "owner_id"          => 0,
                "setting_code"      => "user_id",
                "setting_value"     => "5022057550",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "herriot_api",
                "owner_id"          => 0,
                "setting_code"      => "herriot_api_key",
                "setting_value"     => "MzRmMDIwNTctYTgzNC00ZWE2LWEzMzItZjAxN2ZkNWQxYjEyZDYzNzU1NGUtZDdlYy05NmM3LTFmNDItOWUyZjEyMjYzOTJi",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "herriot_api",
                "owner_id"          => 0,
                "setting_code"      => "herriot_login",
                "setting_value"     => "vukuz-240408",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
            [
                "owner_type"        => "herriot_api",
                "owner_id"          => 0,
                "setting_code"      => "herriot_password",
                "setting_value"     => "aC9dK7Pu6M",
                "setting_value_alt" => "",
                "created_at"        => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at"        => Carbon::now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
}
