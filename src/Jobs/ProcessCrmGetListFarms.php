<?php

namespace Svr\Core\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Svr\Core\Extensions\Handler\CrmListFarms;

class ProcessCrmGetListFarms
{
    use Queueable;

    /**
     * Execute the job.
     *
    */
    public function handle()
    {
        CrmListFarms::getListFarms();
    }
}
