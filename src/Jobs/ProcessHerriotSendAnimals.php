<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Svr\Core\Extensions\Handler\HerriotSendAnimals;

class ProcessHerriotSendAnimals implements ShouldQueue
{
    use Queueable;
	protected array $application_animal_data;        // массив данных (аттрибутов) животного в заявке

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
     * @throws ConnectionException
     */
    public function handle(): void
    {
        HerriotSendAnimals::sendAnimal($this->application_animal_data);
    }
}
