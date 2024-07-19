<?php

return [

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    'channels' => [
        'requestlog' => [
            'driver' => 'daily',
            'path' => storage_path('logs/request/request.log'),
            'level' => 'notice',
        ],
        'custom' => [
            'driver' => 'custom',
            'via' => App\Logging\CustomLogger::class,
            'level' => 'debug',
        ],
    ],

];
