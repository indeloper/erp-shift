<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\Task\NewTasksFromDeletedUserNotice;

class UserObserver
{
    /**
     * Handle the user "stored" event.
     *
     * @return void
     */
    public function saved(User $user)
    {
        if ($this->isDeleted($user)) {
            return $this->notificationAfterUserRemove($user);
        }
    }

    public function notificationAfterUserRemove($user)
    {
        NewTasksFromDeletedUserNotice::send(
            $user->role_codes,
            [
                'name' => 'Пользователь '.$user->long_full_name.' был удалён из системы. С новыми задачами можно ознакомиться здесь: ',
                'additional_info' => ', со списком проектов: ',
                'url' => route('users::card', $user->role_codes),
                'tasks_url' => route('tasks::index'),
            ]
        );
    }

    public function isDeleted(User $user): bool
    {
        return $user->is_deleted and is_string($user->role_codes);
    }
}
