<?php

namespace Svr\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class QueueMonitor extends Command
{
    protected $signature = 'queue:monitor';
    protected $description = 'Monitor the status of the queues';

    public function handle(): void
    {
        $this->info('Monitoring queues:');

        // Выводим информацию о неудачных заданиях
        Artisan::call('queue:failed');
        $this->line(Artisan::output());

        // Вы можете добавить дополнительную логику для мониторинга других аспектов очередей
    }
}
