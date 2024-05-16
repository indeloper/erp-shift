<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TechnicalMaintence\TechnicalMaintenanceNotice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TechUpdatesNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notify {start_date} {start_time} {finish_date} {finish_time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $message = 'Техническая поддержка. '.'C '.$this->argument('start_date').' '.$this->argument('start_time').
                ' по '.$this->argument('finish_date').' '.$this->argument('finish_time').
                ' в ERP-системе (ТУКИ) будут проводиться технические работы. Сервис может быть временно недоступен.';
        $usersIds = User::all()->pluck('id')->toArray();

        TechnicalMaintenanceNotice::send(
            $usersIds,
            [
                'name' => $message,
                'created_at' => Carbon::now(),
            ]
        );
    }
}
