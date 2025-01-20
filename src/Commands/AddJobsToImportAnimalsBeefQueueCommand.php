<?php

namespace Svr\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Handler\ImportBeef;

class AddJobsToImportAnimalsBeefQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animals:send-beef';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавьте задания для импорта очереди животных мясного КРС из RAW в DATA';

    /**
     * Execute the console command.
     */
    public function handle(): bool
    {
        try {
            ImportBeef::addSendAnimalBeefQueue();
        } catch (Exception $e) {
            Log::channel('import_beef')->error('ImportMilk::addSendAnimalBeefQueue() Failed to send animals: ' . $e->getMessage());
            return false;
        }
        return true;
    }
}

