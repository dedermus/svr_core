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
    protected $description = 'Список всех работающих обработчиков очереди и запущенных в фоне планировщиков. Команда работает, если запущена на машине где запущены воркеры';

    protected $help = 'Список всех работающих работников очереди. Команда работает, если запущена на машине где запущены воркеры. Пример команды: php artisan svr:workers:list';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $PHP_OS = strtoupper(substr(PHP_OS, 0, 3));
        $this->info('Операционная система: ' . $PHP_OS);

        // Проверяем, доступна ли команда tasklist
        if ($PHP_OS === 'WIN') {
            // Команда для выполнения
            $cli_command = "wmic process where name='php.exe' get ProcessId,CommandLine";
        } else {
            if (!shell_exec('command -v ps') || !shell_exec('command -v grep')) {
                $this->error('Команды ps или grep недоступны. Убедитесь, что они установлены.');
                return false;
            }
            // $cli_command = 'ps aux | grep artisan | grep -v grep | awk -v spaces="  " \'BEGIN {OFS=","} {print $1, $2, $5 spaces $6 $7}\'';
            $cli_command = 'ps aux';
        }

        // Используем системную команду для поиска запущенных воркеров
        $process = new Process(explode(' ', $cli_command));
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Не удалось получить список воркеров.');
            return false;
        }

        // Получаем вывод команды
        $output_cli = $process->getOutput();

        if ($PHP_OS === 'WIN') {
            $lines = $this->formated_output_for_windows($output_cli);
            $workers = $this->rows_table_windows($lines);
        }
        if ($PHP_OS === 'LIN') {

            $lines = $this->formated_output_for_linux($output_cli);
            $workers = $this->rows_table_linux($lines);
        }
        $this->print_table_to_console($workers);
    }

    private static function convertEncoding($string, $fromEncoding, $toEncoding = 'UTF-8'): array|false|string|null
    {
        return mb_convert_encoding($string, $toEncoding, $fromEncoding);
    }

    /**
     * Форматирование вывода из консоли в среде Windows.
     */
    private function formated_output_for_windows($output): array
    {
        // Удаляем нулевые байты из вывода
        $output = str_replace("\0", '', $output);

        // Преобразуем кодировку вывода консоли в UTF-8
        $output = self::convertEncoding($output, 'CP866', 'UTF-8');

        $lines = explode("\n", $output);
        $result = [];
        foreach ($lines as $line) {
            // Пропускаем пустые строки
            if (empty(trim($line))) {
                continue;
            }
            // Пропускаем заголовок
            if (strpos(strtoupper($line), strtoupper('CommandLine')) === true) {
                continue;
            }

            // Пропускаем если нет в строке 'php'
            if (strpos($line, 'php') === false) {
                continue;
            }

            // ропускаем если нет в строке 'artisan schedule' или 'artisan queue'
            if (strpos($line, 'artisan schedule') === false && strpos($line, 'artisan queue') === false) {
                continue;
            }

            // Удаляем кавычки (одинарные или двойные) только вокруг слова "artisan"
            $line = preg_replace('/[\'"]artisan[\'"]/', 'artisan', $line);

            // Удаляем путь до php.exe и слово php
            $line = preg_replace('/"[^"]*php\.exe"\s*|^php\s+/', '', $line);

            // Убираем лишние пробелы в начале и конце строки
            $line = trim($line);
            // добавляем в result $line
            $result[] = $line;
        }
        return $result;
    }

    /**
     * Форматирование вывода из консоли в среде Linux
     */
    private function formated_output_for_linux($output): array
    {
        $result = [];
        // Фильтруем вывод с помощью PHP
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            // Пропускаем пустые строки
            if (empty($line)) {
                continue;
            }
            // ропускаем если нет в строке 'artisan schedule' или 'artisan queue'
            if (strpos($line, 'artisan schedule') === false && strpos($line, 'artisan queue') === false) {
                continue;
            }
            // добавляем в result $line
            $result[] = $line;
        }
        return $result;
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
        // Если в команде есть queue:work
        if (strpos($command, 'queue:work') !== false) {
            return $matches[1] ?? 'default';
        } else {
            return $matches[1] ?? '';
        }
    }

    /**
     *Создание массива строк для таблицы.
     */
    private function rows_table_windows($lines): array
    {
        $workers = [];
        foreach ($lines as $line) {
            // Используем регулярное выражение для извлечения команды и числа
            if (preg_match('/^(.*?)\s+(\d+)$/', $line, $matches)) {
                $command = trim($matches[1]); // Всё до последнего числа — это команда
                $processId = (int)$matches[2]; // Последнее число — это processId

                // Определяем тип команды (schedule или queue)
                if (str_contains($command, 'schedule')) {
                    $command = 'php artisan schedule:work';
                }

                // Извлекаем очередь из команды
                $queue = $this->extractQueueFromCommand($command);

                $workers[] = [
                    'pid' => $processId,        // PID процесса
                    'user' => "",           // Пользователь (недоступно в Windows)
                    'command' => $command,     // Команда
                    'queue' => $queue,         // Очередь
                ];
            }
        }
        return $workers;
    }

    /**
     * Создание массива строк для таблицы.
     */
    private function rows_table_linux($lines): array
    {
        $workers = [];

        foreach ($lines as $line) {
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
        return $workers;
    }

    /**
     * Печать таблицы в консоль
     */
    private function print_table_to_console(array $workers): void
    {
        if (empty($workers)) {
            $this->info('No workers found.');
        } else {
            $this->info('Running workers:');
            $headers = ['PID', 'USER', 'COMMAND', 'QUEUE'];
            $this->table($headers, $workers);
        }
    }
}
