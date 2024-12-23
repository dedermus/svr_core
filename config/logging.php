<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [
        // канал логирования
        'email' => [
            'driver' => 'single',
            'path' => storage_path('logs/email.log'),
            'level' => 'info', // Уровень логирования
        ],
    ],

];
