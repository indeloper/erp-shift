<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Task;
use App\Notifications\Task\DelayedTaskAddedAgainNotice;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
     */
    public function handle(): void
    {
        $tasks_to_revive = Task::where('revive_at', '<', Carbon::now())->get();

        foreach ($tasks_to_revive as $task) {

            $task->is_solved = 0;
            $task->revive_at = null;
            $task->expired_at = Carbon::now()->addHours(Carbon::create($task->expired_at)->diffInHours($task->created_at));
            $task->save();

            DelayedTaskAddedAgainNotice::send(
                $task->responsible_user_id,
                [
                    'name' => 'Новая задача «'.$task->name.'»',
                    'additional_info' => ' Ссылка на задачу: ',
                    'url' => $task->task_route(),
                    'task_id' => $task->id,
                    'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                    'project_id' => $task->project_id ? $task->project_id : null,
                    'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                ]
            );

            $this->info('task '.$task->name.' was revived');
        }
    }
}
