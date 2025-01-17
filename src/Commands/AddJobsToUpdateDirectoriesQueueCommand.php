<?php

namespace Svr\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Handler\HerriotUpdateDirectories;

class AddJobsToUpdateDirectoriesQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'herriot:update-directories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавьте задания для обновления очереди обновления справочников из хорриот';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        Log::channel('herriot_directories')->info('Task started at: ' . now());
        try {
            HerriotUpdateDirectories::addUpdateDirectoriesQueue();
        } catch (Exception $e) {
            Log::channel('herriot_directories')->error('HerriotUpdateDirectories::addUpdateDirectoriesQueue() Failed to update directories: ' . $e->getMessage());
            return false;
        }
        Log::channel('herriot_directories')->info('Task finished at: ' . now());
        return true;
    }
}
