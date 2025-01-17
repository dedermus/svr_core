<?php

namespace Svr\Core\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Класс консольной команды artisan для вывода списка воркеров
 * Работает только в ОС Linux
 */
class ListWorkers extends Command
{
    //TODO Необходимо реализовать рабочий вариант для ОС Windows

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
        // Определяем операционную систему
        $os = strtoupper(substr(PHP_OS, 0, 3));

        // Проверяем, доступны ли команды ps или wmic в зависимости от ОС
        if ($os === 'LIN') {
            if (!shell_exec('command -v ps') || !shell_exec('command -v grep')) {
                $this->error('Команды ps или grep недоступны. Убедитесь, что они установлены.');
                return;
            }
        } elseif ($os === 'WIN') {
            if (!shell_exec('where wmic')) {
                $this->error('Команда wmic недоступна. Убедитесь, что она установлена.');
                return;
            }
        } else {
            $this->error('Операционная система не поддерживается.');
            return;
        }

        // Используем системную команду для поиска запущенных воркеров
        if ($os === 'LIN') {
            $this->info('OS: LINUX');
            $process = new Process(['ps', 'aux']);
        } elseif ($os === 'WIN') {
            $this->info('OS: WINDOWS');
            $process = new Process(['wmic', 'process', 'get', 'name,commandline,processid']);
        }

        // Используем системную команду для поиска запущенных воркеров
        $process->run();
        if (!$process->isSuccessful()) {
            $this->error('Не удалось получить список воркеров.');
            return;
        }

        // Получаем вывод команды
        $output = $process->getOutput();

        // Фильтруем вывод с помощью PHP
        $lines = explode("\n", $output);
        $lines = array_filter($lines);

        // Ищем строки, содержащие "queue:work"
        $workers = [];

        foreach ($lines as $line) {
            // Пропускаем пустые строки
            if (empty($line)) {
                continue;
            }

            // Для Windows
            if ($os === 'WIN') {
                $parts = str_getcsv($line);
                if (strpos($parts[0], 'php') !== false && strpos($parts[0], 'queue:work') !== false) {
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
            $this->info('Воркеры не найдены.');
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
