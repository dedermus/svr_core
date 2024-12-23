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
        // Проверяем, доступны ли команды ps и grep
        if (!shell_exec('command -v ps') || !shell_exec('command -v grep')) {
            $this->error('Команды ps или grep недоступны. Убедитесь, что они установлены.');
            return;
        }

        // Используем системную команду для поиска запущенных воркеров
        $process = new Process(['ps', 'aux']);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Не удалось получить список воркеров.');
            return;
        }

        // Получаем вывод команды ps aux
        $output = $process->getOutput();

        // Фильтруем вывод с помощью PHP
        $lines = explode("\n", $output);
        $workers = [];

        foreach ($lines as $line) {
            // Пропускаем пустые строки
            if (empty($line)) {
                continue;
            }
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
                    'command' => $command,      // Команда
                    'queue' => $queue,          // Очередь
                ];
            }
        }

        // Выводим информацию о воркерах в консоль. Оформляем в виде таблицы.
        if (empty($workers)) {
            $this->info('No workers found.');
        } else {
            $this->info('Running workers:');
            $headers = ['PID', 'User', 'Command', 'Queue'];
            // Метод фреймворка Laravel table выводит таблицу в виде красивой таблицы
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
