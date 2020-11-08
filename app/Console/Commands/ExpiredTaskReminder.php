<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Task;
use Illuminate\Console\Command;

class ExpiredTaskReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expired:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates notification for users who missed task';

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
        $open_tasks = Task::where('status', 36)->where('is_solved', 0)->get();
        $resp_user_ids = $open_tasks->pluck('responsible_user_id')->values()->unique();
        if ($resp_user_ids) {
            foreach ($resp_user_ids as $user_id) {
                $notification = new Notification();
                $notification->save();
                $notification->additional_info = ' Ссылка на задачи: ' . route('tasks::index');

                $notification->update([
                    'name' => 'Напоминаем, что за несвоевременное заполнение табеля использования технических средств, а также за невыполнение  в срок других задач  назначается штраф в виде снижения КТУ',
                    'user_id' => $user_id,
                    'type' => 111,
                ]);
            }
        }
    }
}
