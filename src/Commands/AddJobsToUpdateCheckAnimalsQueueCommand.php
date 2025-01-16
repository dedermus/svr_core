<?php

namespace Svr\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Handler\HerriotCheckSendAnimals;
use Svr\Core\Extensions\Handler\HerriotSendAnimals;

class AddJobsToUpdateCheckAnimalsQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'herriot:check-send-animals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавьте задания для обновления очереди животных в HerriotCheckSendAnimals';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        Log::channel('herriot_animals_check')->info('Task started at: ' . now());
        try {
            HerriotCheckSendAnimals::addCheckSendAnimalQueue();
        } catch (Exception $e) {
            Log::channel('herriot_animals_check')->error('HerriotCheckSendAnimals::addCheckSendAnimalQueue() Failed to check animals: ' . $e->getMessage());
            return false;
        }
        Log::channel('herriot_animals_check')->info('Task finished at: ' . now());
        return true;
    }
}
