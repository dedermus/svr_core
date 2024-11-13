<?php

namespace Svr\Core\Seeders;

use Illuminate\Database\Seeder;
use Svr\Core\Models\SystemUsersRoles;

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
        (new SystemRolesRightSeeder)->run();
        (new SystemUsersRolesSeeder)->run();
    }
}
