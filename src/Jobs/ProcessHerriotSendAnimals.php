<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\HerriotSendAnimals;

class ProcessHerriotSendAnimals implements ShouldQueue
//class ProcessHerriotSendAnimals
{
    use Queueable;
	protected $application_animal_data;        // ID животного в заявке

	/**
	 * Create a new job instance.
	 */
	public function __construct($application_animal_data)
	{
		$this->application_animal_data = $application_animal_data;
	}

    /**
     * Execute the job.
     *
    */
    public function handle(): void
    {
        HerriotSendAnimals::sendAnimal($this->application_animal_data);
    }
}
