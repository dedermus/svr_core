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
        'svr.email' => [
            'driver' => 'single',
            'path' => storage_path('logs/email.log'),
            'level' => 'info', // Уровень логирования
        ],
		// канал логирования
		'svr.herriot_directories' => [
			'driver' => 'single',
			'path' => storage_path('logs/herriot_directories.log'),
			'level' => 'info', // Уровень логирования
		],
		// канал логирования
		'svr.herriot_companies' => [
			'driver' => 'single',
			'path' => storage_path('logs/herriot_companies.log'),
			'level' => 'info', // Уровень логирования
		],
        // канал логирования
        'svr.herriot_companies_objects' => [
            'driver' => 'single',
            'path' => storage_path('logs/herriot_companies_objects.log'),
            'level' => 'info', // Уровень логирования
        ],
        // канал логирования
        'svr.herriot_animals_send' => [
            'driver' => 'single',
            'path' => storage_path('logs/herriot_animals_send.log'),
            'level' => 'info', // Уровень логирования
        ],
    ],
];
