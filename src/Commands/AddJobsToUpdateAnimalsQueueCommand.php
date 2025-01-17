<?php

namespace Svr\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Handler\HerriotSendAnimals;

class AddJobsToUpdateAnimalsQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'herriot:send-animals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавьте задания для обновления очереди животных в HerriotSendAnimals';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        Log::channel('herriot_animals_send')->info('Task started at: ' . now());
        try {
            HerriotSendAnimals::addSendAnimalQueue();
        } catch (Exception $e) {
            Log::channel('herriot_animals_send')->error('HerriotSendAnimals::addJobsToUpdateAnimalsQueue() Failed to send animals: ' . $e->getMessage());
            return false;
        }
        Log::channel('herriot_animals_send')->info('Task finished at: ' . now());
        return true;
    }
}

