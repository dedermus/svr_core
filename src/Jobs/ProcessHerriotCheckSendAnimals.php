<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\HerriotCheckSendAnimals;

class ProcessHerriotCheckSendAnimals implements ShouldQueue
{
    use Queueable;
	protected array $application_animal_data;        // массив данных о животном

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
        HerriotCheckSendAnimals::checkSendAnimal($this->application_animal_data);
    }
}
