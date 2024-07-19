<?php

namespace App\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CustomLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return Logger
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('custom');

        $logFile = storage_path('logs/laravel.log');
        $archiveFile = storage_path('logs/laravel-archive.log');

        $streamHandler = new StreamHandler($logFile, Logger::DEBUG);

        $rotatingHandler = new RotatingFileHandler($archiveFile, 0, Logger::DEBUG, true, 0666);

        $logger->pushHandler($streamHandler);
        $logger->pushHandler($rotatingHandler);

        return $logger;
    }
}
