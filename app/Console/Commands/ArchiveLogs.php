<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArchiveLogs extends Command
{
    protected $signature = 'logs:archive';
    protected $description = 'Archive and clear the main log file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $logFile = storage_path('logs/laravel.log');
        $archiveFile = storage_path('logs/laravel-'.now()->format('Y-m-d').'.log');

        if (file_exists($logFile)) {
            file_put_contents($archiveFile, file_get_contents($logFile), FILE_APPEND);
            file_put_contents($logFile, '');
        }

        $this->info('Logs have been archived and the main log file has been cleared.');
    }
}

