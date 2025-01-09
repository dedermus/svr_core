<?php

namespace Svr\Core\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Класс консольной команды artisan для запуска воркера очереди с ограничением количества воркеров на очередь.
 * Работает только в ОС Linux.
 */
class StartQueueWorker extends Command
{
    //TODO Необходимо реализовать рабочий вариант для ОС Windows

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'svr:queue:start-worker {queue} {--limit=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запустить воркера очереди с ограничением';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Определяем операционную систему
        $os = strtoupper(substr(PHP_OS, 0, 3));
        if ($os === 'LIN') {
            $this->info('OS: LINUX');
        } elseif ($os === 'WIN') {
            $this->error('OS: WINDOWS');
            $this->error('Команда для запуска воркера очереди: php artisan queue:work работает только на LINUX');
        }

        $queue = $this->argument('queue');
        $limit = $this->option('limit');

        // Проверяем, доступны ли команды ps и grep
        if (!shell_exec('command -v ps') || !shell_exec('command -v grep')) {
            $this->error('Команды ps или grep недоступны. Убедитесь, что они установлены.');
            return;
        }

        // Проверяем, сколько воркеров уже запущено для данной очереди
        $this->info("Старт скрипта создания воркера очереди: $queue");

        // Получаем список запущенных воркеров
        $workers = $this->getRunningWorkers();
        $count_running_worker = 0;
        foreach ($workers as $worker) {
            if ($worker['queue'] == $queue) {
                $count_running_worker = $count_running_worker + 1;
            }
        }
        // Если количество запущенных воркеров меньше лимита, запускаем новый
        if ($count_running_worker < $limit) {
            $this->info("Запуск воркера для очереди: $queue");

            // Команда для запуска воркера
            $command = "php artisan queue:work --queue=$queue";
            $process = new Process(explode(' ', $command));

            $process->setTimeout(null); // Отключаем тайм-аут для процесса

            $process->run();
        } else {
            $this->info("Достигнут лимит: $limit количества воркеров для очереди: $queue");
            $workers = $this->getRunningWorkers();
            $this->displayWorkersTable($workers);
        }
    }

    /**
     * Получает список запущенных воркеров для указанной очереди.
     *
     * @return array
     */
    private function getRunningWorkers(): array
    {
        // Используем ps aux для получения списка процессов
        $process = new Process(['ps', 'aux']);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Не удалось выполнить команду ps aux: ' . $process->getErrorOutput());
            return [];
        }

        // Получаем вывод команды ps aux
        $output = $process->getOutput();
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
                $queueName = $this->extractQueueFromCommand($command);

                // Добавляем информацию о воркере в массив
                $workers[] = [
                    'pid' => $parts[1],         // PID процесса
                    'user' => $parts[2],        // Пользователь
                    'command' => $command,      // Команда
                    'queue' => $queueName,      // Очередь
                ];
            }
        }
        return $workers;
    }

    /**
     * Выводит таблицу запущенных воркеров.
     *
     * @param array $workers
     */
    private function displayWorkersTable(array $workers): void
    {
        if (!empty($workers)) {
            $this->table(
                ['PID', 'User', 'Command', 'Queue'],
                $workers
            );
        } else {
            $this->info('Нет запущенных воркеров.');
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
