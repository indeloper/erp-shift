<?php

namespace App\Observers;

use App\Domain\Enum\NotificationType;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskObserver
{
    /**
     * Handle the task "stored" event.
     *
     * @param  Task  $task
     * @return void
     */
    public function saved(Task $task)
    {
        if ($task->wasRecentlyCreated) {
            if ($task->status == 21) {
                return $this->notificationForWriteOffControlTask($task);
            }
        }
    }

    public function notificationForWriteOffControlTask(Task $task)
    {
        dispatchNotify(
            $task->responsible_user_id,
            'Новая задача «' . $task->name . '»',
            '',
            NotificationType::WRITE_OFF_CONTROL_TASK_CREATED_NOTIFICATION,
            [
                'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                'task_id' => $task->id,
            ]
        );
    }
}
