<?php

namespace Svr\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Handler\ImportMilk;

class AddJobsToImportAnimalsMilkQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animals:send-milk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавьте задания для импорта очереди животных молочного КРС из RAW в DATA';

    /**
     * Execute the console command.
     */
    public function handle(): bool
    {
        try {
            ImportMilk::addSendAnimalMilkQueue();
        } catch (Exception $e) {
            Log::channel('import_milk')->error('ImportMilk::addSendAnimalMilkQueue() Failed to send animals: ' . $e->getMessage());
            return false;
        }
        return true;
    }
}

