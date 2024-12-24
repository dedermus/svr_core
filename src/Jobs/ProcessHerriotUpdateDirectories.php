<?php

namespace Svr\Core\Jobs;

use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\HerriotUpdateDirectories;

class ProcessHerriotUpdateDirectories
{
    use Queueable;

    /**
     * Execute the job.
     *
    */
    public function handle()
    {
        HerriotUpdateDirectories::getDirectories();
    }
}
