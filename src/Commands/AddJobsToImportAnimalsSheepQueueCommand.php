<?php

namespace Svr\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Handler\ImportSheep;

class AddJobsToImportAnimalsSheepQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'animals:send-sheep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавьте задания для импорта очереди животных (овец) МРС из RAW в DATA';

    /**
     * Execute the console command.
     */
    public function handle(): bool
    {
        try {
            ImportSheep::addSendAnimalSheepQueue();
        } catch (Exception $e) {
            Log::channel('import_sheep')->error('ImportMilk::addSendAnimalSheepQueue() Failed to send animals: ' . $e->getMessage());
            return false;
        }
        return true;
    }
}

