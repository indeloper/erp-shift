<?php

namespace App\Observers;

use App\Domain\Enum\NotificationType;
use App\Models\Notification;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the user "stored" event.
     *
     * @param  User  $user
     * @return void
     */
    public function saved(User $user)
    {
        if ($this->isDeleted($user))
            return $this->notificationAfterUserRemove($user);
    }

    public function notificationAfterUserRemove($user)
    {
        dispatchNotify(
            $user->role_codes,
            'Пользователь ' . $user->long_full_name . ' был удалён из системы. С новыми задачами можно ознакомиться здесь: ' .
            route('tasks::index') . ', со списком проектов: ' . route('users::card', $user->role_codes),
            '',
            NotificationType::NEW_TASKS_FROM_DELETED_USER_NOTIFICATION
        );
    }

    public function isDeleted(User $user): bool
    {
        return $user->is_deleted and is_string($user->role_codes);
    }
}
