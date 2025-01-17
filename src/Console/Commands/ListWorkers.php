<?php

namespace Svr\Core\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Класс консольной команды artisan для вывода списка воркеров
 */
class ListWorkers extends Command
{
    /**
     * Имя и подпись консольной команды.
     *
     * @var string
     */
    protected $signature = 'svr:workers:list';

    /**
     * Описание консольной команды.
     *
     * @var string
     */
    protected $description = 'Список всех работающих работников очереди. Команда работает, если запущена на машине где запущены воркеры';

    protected $help = 'Список всех работающих работников очереди. Команда работает, если запущена на машине где запущены воркеры. Пример команды: php artisan svr:workers:list';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $PHP_OS = strtoupper(substr(PHP_OS, 0, 3));
        // Проверяем, доступна ли команда tasklist
        if ($PHP_OS === 'WIN') {
            // Команда для выполнения
            $command = "wmic process where name='php.exe' get ProcessId,CommandLine,ExecutablePath";
        } else {
            if (!shell_exec('command -v ps') || !shell_exec('command -v grep')) {
                $this->error('Команды ps или grep недоступны. Убедитесь, что они установлены.');
                return;
            }
            $command = 'ps aux';
        }

        // Используем системную команду для поиска запущенных воркеров
        $process = new Process(explode(' ', $command));
        $process->run();
        echo $process->getOutput();
        if (!$process->isSuccessful()) {
            $this->error('Не удалось получить список воркеров.');
            return;
        }

        // Получаем вывод команды
        $output = $process->getOutput();

/*
В винде
D:\OSPanel\home\laravel.plinor.local>wmic process where "name='php.exe'" get ProcessId,CommandLine,ExecutablePath
CommandLine                                                                          ExecutablePath                          ProcessId
php  artisan schedule:work                                                           D:\OSPanel\modules\PHP-8.3\PHP\php.exe  9336
"D:\OSPanel\modules\PHP-8.3\PHP\php.exe"  "artisan" queue:work --queue=crm           D:\OSPanel\modules\PHP-8.3\PHP\php.exe  18032
"D:\OSPanel\modules\PHP-8.3\PHP\php.exe"  "artisan" queue:work --queue=email         D:\OSPanel\modules\PHP-8.3\PHP\php.exe  6676
"D:\OSPanel\modules\PHP-8.3\PHP\php.exe"  "artisan" queue:work --queue=import_milk   D:\OSPanel\modules\PHP-8.3\PHP\php.exe  17520
"D:\OSPanel\modules\PHP-8.3\PHP\php.exe"  "artisan" queue:work --queue=import_beef   D:\OSPanel\modules\PHP-8.3\PHP\php.exe  18288
"D:\OSPanel\modules\PHP-8.3\PHP\php.exe"  "artisan" queue:work --queue=import_sheep  D:\OSPanel\modules\PHP-8.3\PHP\php.exe  21336
php  artisan queue:work --queue=import_milk                                          D:\OSPanel\modules\PHP-8.3\PHP\php.exe  23168
*/

        // Фильтруем вывод с помощью PHP
        $lines = explode("\n", $output);
        $workers = [];

        foreach ($lines as $line) {
            // Пропускаем пустые строки
            if (empty($line)) {
                continue;
            }

            // Для Windows
            if ($PHP_OS === 'WIN') {
                // Используем регулярное выражение для разбивки строки
                preg_match_all('/"([^"]+)"|(\S+)/', $line, $matches);

                // Объединяем результаты в один массив
                $parts = array_merge(array_filter($matches[1]), array_filter($matches[2]));

                echo '#-----# | '.$parts[3]."\n";
                if (strpos($parts[0], 'php.exe') !== false && strpos($parts[0], 'queue:work') !== false) {
                    echo $parts;
                    $workers[] = [
                        'pid' => $parts[1],         // PID процесса
                        'user' => $parts[7],        // Пользователь
                        'command' => $parts[0],     // Команда
                        'queue' => $this->extractQueueFromCommand($parts[0]), // Очередь
                    ];
                }
            } else {
                // Ищем строки, содержащие "queue:work"
                if (strpos($line, 'queue:work') !== false) {
                    // Разбиваем строку на части
                    $parts = preg_split('/\s+/', $line);

                    // Определяем команду
                    $command = implode(' ', array_slice($parts, 4)); // Собираем команду из оставшихся элементов

                    // Ищем аргументы команды
                    $queue = $this->extractQueueFromCommand($command);

                    // Добавляем информацию о воркере в массив
                    $workers[] = [
                        'pid' => $parts[1],         // PID процесса
                        'user' => $parts[2],        // Пользователь
                        'command' => $command,     // Команда
                        'queue' => $queue,         // Очередь
                    ];
                }
            }
        }

        // Выводим информацию о воркерах в консоль. Оформляем в виде таблицы.
        if (empty($workers)) {
            $this->info('No workers found.');
        } else {
            $this->info('Running workers:');
            $headers = ['PID', 'User', 'Command', 'Queue'];
            $this->table($headers, $workers);
        }
    }

    /**
     * Извлекает название очереди из команды.
     *
     * @param string $command
     * @return string
     */
    private function extractQueueFromCommand(string $command): string
    {
        // Ищем аргумент "--queue="
        preg_match('/--queue=([^\s]+)/', $command, $matches);

        // Возвращаем название очереди или "default"
        return $matches[1] ?? 'default';
    }
}
