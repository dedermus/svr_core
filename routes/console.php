<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
Schedule::command('inspire')->everyTenMinutes()->runInBackground();
Schedule::command('route:list')->dailyAt('02:00');

// Запускаем воркера каждые 2 часа
Schedule::command('queue:work --queue=email')->everyTwoHours();
