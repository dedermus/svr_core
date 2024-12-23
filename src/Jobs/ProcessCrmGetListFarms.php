<?php

namespace Svr\Core\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Svr\Core\Extensions\Handler\CrmListFarms;

class ProcessCrmGetListFarms
{
    use Queueable;
    protected string $token;        // токен

    /**
     * Create a new job instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
    */
    public function handle()
    {
        CrmListFarms::getListFarms();
    }
}
