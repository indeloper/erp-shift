<?php

namespace App\Observers\TechAcc;

use App\Models\TechAcc\OurTechnicTicketReport;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class OurTechnicTicketReportObserver
{
    /**
     * Handle the our technic ticket report "created" event.
     *
     * @param  \App\Models\TechAcc\OurTechnicTicketReport  $ourTechnicTicketReport
     * @return void
     */
    public function created(OurTechnicTicketReport $ourTechnicTicketReport)
    {
        $ourTechnicTicketReport->ticket->comments()->create([
            'comment' =>
                'Добавлен отчет об использовании на ' . $ourTechnicTicketReport->date_carbon .
                ($ourTechnicTicketReport->comment ? '. Комментарий пользователя: ' . $ourTechnicTicketReport->comment : '') .
                '. Время использования: ' . $ourTechnicTicketReport->hours . 'ч.',
            'author_id' => Auth::user()->id,
            'system' => 1,
        ]);
    }

    /**
     * Handle the our technic ticket report "updated" event.
     *
     * @param  \App\Models\TechAcc\OurTechnicTicketReport  $ourTechnicTicketReport
     * @return void
     */
    public function updating(OurTechnicTicketReport $ourTechnicTicketReport)
    {
        $comment = 'Изменен отчет об использовании';

        foreach ($ourTechnicTicketReport->getDirty() as $key => $value) {
            if ($key == 'hours') {
                $comment.= '. Предыдущее кол-во часов: ' . $ourTechnicTicketReport->getOriginal()['hours'] . '. Новое: ' . $value;
            } elseif ($key == 'comment') {
                $comment.= '. Предыдущий комментарий: ' . $ourTechnicTicketReport->getOriginal()['comment'] . '. Новый: ' . $value;
            } else {
                $comment.= '. Предыдущая дата: ' . Carbon::parse($ourTechnicTicketReport->getOriginal()['date'])->format('d.m.Y') . '. Новая: ' . Carbon::parse($value)->format('d.m.Y');
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
     * @param  \App\Models\TechAcc\OurTechnicTicketReport  $ourTechnicTicketReport
     * @return void
     */
    public function deleted(OurTechnicTicketReport $ourTechnicTicketReport)
    {
        $ourTechnicTicketReport->ticket->comments()->create([
            'comment' => 'Удален отчет об использовании на ' . Carbon::parse($ourTechnicTicketReport->date)->format('d.m.Y'),
            'author_id' => Auth::user()->id,
            'system' => 1,
        ]);
    }
}
