<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\HerriotUpdateCompanies;

//class ProcessHerriotUpdateCompanies implements ShouldQueue
class ProcessHerriotUpdateCompanies
{
    use Queueable;
	protected string $company_id;        // ID компании

	/**
	 * Create a new job instance.
	 */
	public function __construct($company_id)
	{
		$this->company_id = $company_id;
	}

    /**
     * Execute the job.
     *
    */
    public function handle()
    {
        HerriotUpdateCompanies::getCompanies($this->company_id);
    }
}
