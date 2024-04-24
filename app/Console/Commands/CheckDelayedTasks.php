<?php

namespace App\Console\Commands;

use App\Domain\Enum\NotificationType;
use App\Events\NotificationCreated;
use App\Models\Notification;
use App\Models\Notification\Notification;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDelayedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:checkDelayed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Checks if it's time for task to be created again";

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
        DB::beginTransaction();

        $tasks_to_revive = Task::where('revive_at', '<', Carbon::now())->get();

        foreach ($tasks_to_revive as $task) {

            $task->is_solved = 0;
            $task->revive_at = null;
            $task->expired_at = Carbon::now()->addHours(Carbon::create($task->expired_at)->diffInHours($task->created_at));
            $task->save();

            dispatchNotify(
                $task->responsible_user_id,
                'Новая задача «' . $task->name . '»',
                '',
                NotificationType::DELAYED_TASK_ADDED_AGAIN_NOTIFICATION,
                [
                    'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                    'task_id' => $task->id,
                    'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                    'project_id' => $task->project_id ? $task->project_id : null,
                    'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null
                ]
            );

            $this->info('task '. $task->name .' was revived');
        }

        DB::commit();
    }
}
