<?php

use Illuminate\Support\Facades\Schedule;
use Svr\Core\Jobs\ProcessCrmGetListCountries;
use Svr\Core\Jobs\ProcessCrmGetListDistricts;
use Svr\Core\Jobs\ProcessCrmGetListFarms;
use Svr\Core\Jobs\ProcessCrmGetListRegions;
use Svr\Core\Jobs\ProcessCrmGetListUsers;
use Svr\Core\Jobs\ProcessImportMilk;

Schedule::job(new ProcessImportMilk, 'import_milk')->everyMinute()->description('Импорт животных молочного КРС');
//Schedule::job(new ProcessImportBeef, 'import_beef')->everyMinute()->description('Импорт животных мясного КРС');
//Schedule::job(new ProcessImportSheep, 'import_sheep')->everyMinute()->description('Импорт животных (овец) МРС');

Schedule::job(new ProcessCrmGetListCountries, 'crm')->dailyAt('00:10')->description('Обновление справочников стран из CRM');
Schedule::job(new ProcessCrmGetListRegions, 'crm')->dailyAt('00:30')->description('Обновление справочников регионов/областей из CRM');
Schedule::job(new ProcessCrmGetListDistricts, 'crm')->dailyAt('00:50')->description('Обновление справочников районов из CRM');
Schedule::job(new ProcessCrmGetListFarms, 'crm')->dailyAt('01:10')->description('Обновление справочников компаний из CRM');
Schedule::job(new ProcessCrmGetListUsers, 'crm')->dailyAt('02:30')->description('Обновление справочников пользователей из CRM');

Schedule::command('herriot:update-directories')->everyMinute()->runInBackground()->description('Обновление справочников из Хорриота');
Schedule::command('herriot:update-company')->everyMinute()->runInBackground()->description('Обновление компаний из Хорриота');
Schedule::command('herriot:update-company-objects')->everyMinute()->runInBackground()->description('Обновление поднадзорных объектов из Хорриота');
Schedule::command('herriot:send-animals')->everyMinute()->runInBackground()->description('Отправка животных на регистрацию в Хорриот');
Schedule::command('herriot:check-send-animals')->everyMinute()->runInBackground()->description('Проверка статуса регистрации животных в Хорриот');

