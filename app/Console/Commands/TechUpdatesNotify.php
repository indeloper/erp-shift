<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Notification;
use App\Models\User;

use App\Events\NotificationCreated;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $notifications = [];
        $message = 'Техническая поддержка. '. 'C ' . $this->argument('start_date') . ' ' . $this->argument('start_time') . ' по ' . $this->argument('finish_date') . ' ' . $this->argument('finish_time') . ' будут проводиться технические работы. Сервис может быть временно недоступен.';
        DB::beginTransaction();
        foreach (User::all() as $user) {
            $notification = Notification::create([
                'name' => $message,
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'type' => 14
            ]);
        }

        DB::commit();
    }
}
