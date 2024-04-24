<?php

namespace App\Console\Commands;

use App\Domain\Enum\NotificationType;
use Illuminate\Console\Command;
use App\Models\Notification\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $notifications = [];
        $message = 'Техническая поддержка. Работы были закончены досрочно. Сервис снова доступен.';
        DB::beginTransaction();
        foreach (User::all() as $user) {
            dispatchNotify(
                $user->id,
                $message,
                '',
                NotificationType::TECHNICAL_MAINTENANCE_COMPLETION_NOTICE,
                [
                    'created_at' => Carbon::now(),
                ]
            );
        }

        DB::commit();
    }
}
