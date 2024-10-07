<?php

namespace Svr\Core;

use OpenAdminCore\Admin\Extension;
use OpenAdminCore\Admin\Facades\Admin;

class SvrCore extends Extension
{

    public function bootstrap()
    {
        $this->fireBootingCallbacks();

        require config('admin.bootstrap', admin_path('bootstrap.php'));

        $this->addAdminAssets();

        $this->fireBootedCallbacks();
    }

    public $name = 'svr-core';

    public $views = __DIR__ . '/../resources/views';

}
