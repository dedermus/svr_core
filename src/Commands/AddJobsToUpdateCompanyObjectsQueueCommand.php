<?php

namespace Svr\Core\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Svr\Core\Extensions\Handler\HerriotUpdateCompanies;
use Svr\Core\Extensions\Handler\HerriotUpdateCompaniesObjects;

class AddJobsToUpdateCompanyObjectsQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'herriot:update-company-objects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавьте задания для обновления очереди обновления поднадзорных объектов из хорриот';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        Log::channel('herriot_companies_objects')->info('Task started at: ' . now());
        try {
            HerriotUpdateCompaniesObjects::addCompanyObjectsQueue();
        } catch (Exception $e) {
            Log::channel('herriot_companies_objects')->error('HerriotUpdateCompaniesObjects::addCompanyObjectsQueue() Failed to update company objects: ' . $e->getMessage());
            return false;
        }
        Log::channel('herriot_companies_objects')->info('Task finished at: ' . now());
        return true;
    }
}
