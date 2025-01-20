<?php

namespace Svr\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Handler\ApplicationClose;

class AddJobsToApplicationCloseQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'herriot:application_close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавьте задания для обновления очереди закрытия заявок';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        Log::channel('application_close')->info('Task started at: ' . now());
        try {
			ApplicationClose::addApplicationCloseQueue();
        } catch (Exception $e) {
            Log::channel('application_close')->error('ApplicationClose::addApplicationCloseQueue() Failed to close application: ' . $e->getMessage());
            return false;
        }
        Log::channel('application_close')->info('Task finished at: ' . now());
        return true;
    }
}
