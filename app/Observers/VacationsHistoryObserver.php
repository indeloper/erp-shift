<?php

namespace App\Observers;

use App\Events\NotificationCreated;
use App\Models\Notification;
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

        Notification::create([
            'name' => 'Сообщаем, что с ' . $vacationsHistory->from_date .
                ' по ' . $vacationsHistory->by_date .
                ' вы будете заменять пользователя ' . $vacation_user->long_full_name .
                ', так как он будет в отпуске',
            'user_id' => $vacationsHistory->support_user_id,
            'type' => 46,
        ]);
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

        Notification::create([
            'name' => 'Пользователь ' . $vacation_user->long_full_name .
                ' ушел в отпуск. С новыми задачами можно ознакомиться здесь: на странице задач',
            'user_id' => $vacationsHistory->support_user_id,
            'type' => 47,
        ]);
    }

    public function notifyAboutVacationEnd(VacationsHistory $vacationsHistory)
    {
        $vacation_user = User::findOrFail($vacationsHistory->vacation_user_id);

        Notification::create([
            'name' => 'Пользователь ' . $vacation_user->long_full_name .
                ' вышел из отпуска. Ему вернутся задачи и позиции в проектах',
            'user_id' => $vacationsHistory->support_user_id,
            'type' => 48,
        ]);
    }
}

