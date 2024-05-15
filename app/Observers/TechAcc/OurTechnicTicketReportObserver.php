<?php

namespace App\Observers\TechAcc;

use App\Models\TechAcc\OurTechnicTicketReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OurTechnicTicketReportObserver
{
    /**
     * Handle the our technic ticket report "created" event.
     *
     * @return void
     */
    public function created(OurTechnicTicketReport $ourTechnicTicketReport): void
    {
        $ourTechnicTicketReport->ticket->comments()->create([
            'comment' => 'Добавлен отчет об использовании на '.$ourTechnicTicketReport->date_carbon.
                ($ourTechnicTicketReport->comment ? '. Комментарий пользователя: '.$ourTechnicTicketReport->comment : '').
                '. Время использования: '.$ourTechnicTicketReport->hours.'ч.',
            'author_id' => Auth::user()->id,
            'system' => 1,
        ]);
    }

    /**
     * Handle the our technic ticket report "updated" event.
     *
     * @return void
     */
    public function updating(OurTechnicTicketReport $ourTechnicTicketReport)
    {
        $comment = 'Изменен отчет об использовании';

        foreach ($ourTechnicTicketReport->getDirty() as $key => $value) {
            if ($key == 'hours') {
                $comment .= '. Предыдущее кол-во часов: '.$ourTechnicTicketReport->getOriginal()['hours'].'. Новое: '.$value;
            } elseif ($key == 'comment') {
                $comment .= '. Предыдущий комментарий: '.$ourTechnicTicketReport->getOriginal()['comment'].'. Новый: '.$value;
            } else {
                $comment .= '. Предыдущая дата: '.Carbon::parse($ourTechnicTicketReport->getOriginal()['date'])->format('d.m.Y').'. Новая: '.Carbon::parse($value)->format('d.m.Y');
            }
        }
        $ourTechnicTicketReport->ticket->comments()->create([
            'comment' => $comment,
            'author_id' => Auth::user()->id,
            'system' => 1,
        ]);
    }

    /**
     * Handle the our technic ticket report "deleted" event.
     *
     * @return void
     */
    public function deleted(OurTechnicTicketReport $ourTechnicTicketReport): void
    {
        $ourTechnicTicketReport->ticket->comments()->create([
            'comment' => 'Удален отчет об использовании на '.Carbon::parse($ourTechnicTicketReport->date)->format('d.m.Y'),
            'author_id' => Auth::user()->id,
            'system' => 1,
        ]);
    }
}
