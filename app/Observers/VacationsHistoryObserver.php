<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Vacation\VacationsHistory;
use App\Notifications\Employee\UserLeaveSubstitutionNotice;
use App\Notifications\Task\NewTasksFromUserOnLeaveNotice;
use App\Notifications\Task\SubstituteUserReturnFromLeaveTaskTransferNotice;

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

        UserLeaveSubstitutionNotice::send(
            $vacationsHistory->support_user_id,
            [
                'name' => 'Сообщаем, что с ' . $vacationsHistory->from_date . ' по ' . $vacationsHistory->by_date .
                          ' вы будете заменять пользователя ' . $vacation_user->long_full_name .
                          ', так как он будет в отпуске',
            ]
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

        NewTasksFromUserOnLeaveNotice::send(
            $vacationsHistory->support_user_id,
            [
                'name' => 'Пользователь ' . $vacation_user->long_full_name .
                          ' ушел в отпуск. С новыми задачами можно ознакомиться здесь: на странице задач',
            ]
        );
    }

    public function notifyAboutVacationEnd(VacationsHistory $vacationsHistory)
    {
        $vacation_user = User::findOrFail($vacationsHistory->vacation_user_id);

        SubstituteUserReturnFromLeaveTaskTransferNotice::send(
            $vacationsHistory->support_user_id,
            [
                'name' => 'Пользователь ' . $vacation_user->long_full_name .
                          ' вышел из отпуска. Ему вернутся задачи и позиции в проектах',
            ]
        );
    }
}

