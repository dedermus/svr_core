<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\HerriotSendAnimals;

//class ProcessHerriotSendAnimals implements ShouldQueue
class ProcessHerriotSendAnimals
{
    use Queueable;
	protected string $application_animal_id;        // ID животного в заявке

	/**
	 * Create a new job instance.
	 */
	public function __construct($application_animal_id)
	{
		$this->application_animal_id = $application_animal_id;
	}

    /**
     * Execute the job.
     *
    */
    public function handle(): void
    {
        HerriotSendAnimals::sendAnimal($this->application_animal_id);
    }
}
