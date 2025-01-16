<?php

namespace Svr\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Handler\HerriotUpdateCompanies;

class AddJobsToUpdateCompanyQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'herriot:update-company';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавьте задания для обновления очереди обновления компаний из хорриот';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        Log::channel('herriot_companies')->info('Task started at: ' . now());
        try {
            HerriotUpdateCompanies::addCompanyQueue();
        } catch (Exception $e) {
            Log::channel('herriot_companies')->error('HerriotUpdateCompanies::addCompanyQueue() Failed to update company: ' . $e->getMessage());
            return false;
        }
        Log::channel('herriot_companies')->info('Task finished at: ' . now());
        return true;
    }
}
