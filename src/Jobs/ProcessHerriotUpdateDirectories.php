<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\HerriotUpdateDirectories;

class ProcessHerriotUpdateDirectories implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     *
    */
    public function handle(): void
    {
        HerriotUpdateDirectories::getDirectories();
    }
}
