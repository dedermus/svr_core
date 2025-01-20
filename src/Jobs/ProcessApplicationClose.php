<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\ApplicationClose;

class ProcessApplicationClose implements ShouldQueue
{
    use Queueable;
	protected $application_id;

	/**
	 * Create a new job instance.
	 */
	public function __construct($application_id)
	{
		$this->application_id = $application_id;
	}

    /**
     * Execute the job.
     *
    */
    public function handle(): void
    {
		ApplicationClose::applicationClose($this->application_id);
    }
}
