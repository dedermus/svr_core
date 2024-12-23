<?php

namespace Svr\Core\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Email\CrmListFarms;

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
     */
    public function handle(): void
    {
        CrmListFarms::getListFarms($this->token);
    }
}
