<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

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
