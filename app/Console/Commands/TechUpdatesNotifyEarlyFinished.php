<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TechnicalMaintence\TechnicalMaintenanceCompletionNotice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TechUpdatesNotifyEarlyFinished extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:early_finished_notify';

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
        $message = 'Техническая поддержка. Работы были закончены досрочно. Сервис снова доступен.';
        $usersIds = User::all()->pluck('id')->toArray();

        TechnicalMaintenanceCompletionNotice::send(
            $usersIds,
            [
                'name' => $message,
                'created_at' => Carbon::now(),
            ]
        );
    }
}
