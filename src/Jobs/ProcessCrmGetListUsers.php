<?php

namespace Svr\Core\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\CrmListUsers;

class ProcessCrmGetListUsers
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
    */
    public function handle(): void
    {
        CrmListUsers::getListUsers();
    }
}
