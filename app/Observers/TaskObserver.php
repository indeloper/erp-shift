<?php

namespace App\Observers;

use App\Models\Task;
use App\Notifications\Task\WriteOffControlTaskCreatedNotice;

class TaskObserver
{
    /**
     * Handle the task "stored" event.
     */
    public function saved(Task $task): void
    {
        if ($task->wasRecentlyCreated) {
            if ($task->status == 21) {
                return $this->notificationForWriteOffControlTask($task);
            }
        }
    }

    public function notificationForWriteOffControlTask(Task $task)
    {
        WriteOffControlTaskCreatedNotice::send(
            $task->responsible_user_id,
            [
                'name' => 'Новая задача «'.$task->name.'»',
                'additional_info' => ' Ссылка на задачу: ',
                'url' => $task->task_route(),
                'task_id' => $task->id,
            ]
        );
    }
}
