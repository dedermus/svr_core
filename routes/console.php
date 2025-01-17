<?php

use Illuminate\Support\Facades\Schedule;
use Svr\Core\Jobs\ProcessImportMilk;

Schedule::job(new ProcessImportMilk, 'import_milk')->everyMinute();


Schedule::command('herriot:update-directories')->everyMinute()->runInBackground()->description('Обновление справочников из Хорриота');
Schedule::command('herriot:update-company')->everyMinute()->runInBackground()->description('Обновление компаний из Хорриота');
Schedule::command('herriot:update-company-objects')->everyMinute()->runInBackground()->description('Обновление поднадзорных объектов из Хорриота');
Schedule::command('herriot:send-animals')->everyMinute()->runInBackground()->description('Отправка животных на регистрацию в Хорриот');
Schedule::command('herriot:check-send-animals')->everyMinute()->runInBackground()->description('Проверка статуса регистрации животных в Хорриот');

