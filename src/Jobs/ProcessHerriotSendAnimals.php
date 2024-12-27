<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\HerriotSendAnimals;

//class ProcessHerriotSendAnimals implements ShouldQueue
class ProcessHerriotSendAnimals
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
    public function handle(): void
    {
        HerriotSendAnimals::sendAnimal($this->animal_id);
    }
}
