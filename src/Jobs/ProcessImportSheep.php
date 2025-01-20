<?php

namespace Svr\Core\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Svr\Core\Extensions\Handler\ImportSheep;

/**
 * Обработчик задачи из очереди на импорт животного (овец) МРС из RAW в DATA
 */
class ProcessImportSheep implements ShouldQueue
{
    use Queueable;

    protected int $animal_id;        // ID животного

    /**
     * Create a new job instance.
     */
    public function __construct($animal_id)
    {
        $this->animal_id = $animal_id;
    }

    /**
     * Execute the job.
    */
    public function handle(): void
    {
        ImportSheep::animalsImportSheep($this->animal_id);
    }
}
