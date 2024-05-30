<?php

return [

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    'channels' => [
        'requestlog' => [
            'driver' => 'daily',
            'path' => storage_path('logs/request/request.log'),
            'level' => 'notice',
        ],
    ],

];
