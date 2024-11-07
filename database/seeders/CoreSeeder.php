<?php

namespace Svr\Core\Seeders;

use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        (new SystemUsersSeeder)->run();
        (new SystemModulesSeeder)->run();
        (new SystemModulesActionsSeeder)->run();
        (new SystemRolesSeeder)->run();
        (new SystemSettingsSeeder)->run();
        (new SystemUsersNotificationsMessagesSeeder)->run();
    }
}
