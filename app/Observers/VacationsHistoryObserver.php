<?php

namespace App\Observers;

use App\Domain\Enum\NotificationType;
use App\Models\User;
use App\Models\Vacation\VacationsHistory;

class VacationsHistoryObserver
{
    /**
     * Handle the vacations history "saved" event.
     *
     * @param  VacationsHistory  $vacationsHistory
     * @return void
     */
    public function saved(VacationsHistory $vacationsHistory)
    {
        if ($vacationsHistory->wasRecentlyCreated) {
            return $this->newVacation($vacationsHistory);
        }

        return $this->updatedFork($vacationsHistory);
    }

    public function newVacation(VacationsHistory $vacationsHistory)
    {
        $vacation_user = User::findOrFail($vacationsHistory->vacation_user_id);

        dispatchNotify(
            $vacationsHistory->support_user_id,
            'Сообщаем, что с ' . $vacationsHistory->from_date . ' по ' . $vacationsHistory->by_date .
            ' вы будете заменять пользователя ' . $vacation_user->long_full_name . ', так как он будет в отпуске',
            NotificationType::USER_LEAVE_SUBSTITUTION_NOTIFICATION
        );
    }

    public function updatedFork(VacationsHistory $vacationsHistory)
    {
        if ($vacationsHistory->is_actual) {
            return $this->notifyAboutVacationStart($vacationsHistory);
        }

        return $this->notifyAboutVacationEnd($vacationsHistory);
    }

    public function notifyAboutVacationStart(VacationsHistory $vacationsHistory)
    {
        $vacation_user = User::findOrFail($vacationsHistory->vacation_user_id);

        dispatchNotify(
            $vacationsHistory->support_user_id,
            'Пользователь ' . $vacation_user->long_full_name .
            ' ушел в отпуск. С новыми задачами можно ознакомиться здесь: на странице задач',
            NotificationType::NEW_TASKS_FROM_USER_ON_LEAVE_NOTIFICATION
        );
    }

    public function notifyAboutVacationEnd(VacationsHistory $vacationsHistory)
    {
        $vacation_user = User::findOrFail($vacationsHistory->vacation_user_id);

        dispatchNotify(
            $vacationsHistory->support_user_id,
            'Пользователь ' . $vacation_user->long_full_name .
            ' вышел из отпуска. Ему вернутся задачи и позиции в проектах',
            NotificationType::SUBSTITUTE_USER_RETURN_FROM_LEAVE_TASK_TRANSFER_NOTIFICATION
        );
    }
}

