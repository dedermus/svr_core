<?php

namespace Svr\Core;

use OpenAdminCore\Admin\Admin;
use OpenAdminCore\Admin\Extension;

class CoreManager extends Extension
{

    /**
     * Bootstrap this package.
     *
     * @return void
     */
    public static function boot()
    {

        Admin::extend('svr-core', __CLASS__);
    }

}
