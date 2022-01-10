<?php

namespace App\Observers;

use App\Models\HumanResources\Timecard;
use App\Models\HumanResources\TimecardDay;

class TimecardDayObserver
{
    /**
     * Handle the timecardDay "updated" event.
     *
     * @param  TimecardDay  $timecardDay
     * @return void
     */
    public function updated(TimecardDay $timecardDay): void
    {
        $timecardDay->generateAction('update');
    }

    /**
     * Handle the timecardDay "creating" event.
     *
     * @param  TimecardDay  $timecardDay
     * @return void|bool
     */
    public function creating(TimecardDay $timecardDay)
    {
        // If day from timecardDay not exist in timecard month
        $timecardMonthLastDay = now()->month(Timecard::findOrFail($timecardDay->timecard_id)->month)->lastOfMonth()->day;
        if ($timecardDay->day > $timecardMonthLastDay) {
            // We should abort creation
            return false;
        }
        // Or we already have timecard day in same day
        if (TimecardDay::where('timecard_id', $timecardDay->timecard_id)->where('day', $timecardDay->day)->exists()) {
            // We should abort creation
            return false;
        }
    }

    /**
     * Handle the timecardDay "created" event.
     *
     * @param  TimecardDay  $timecardDay
     * @return void
     */
    public function created(TimecardDay $timecardDay): void
    {
        $timecardDay->generateAction();
    }

    /**
     * Handle the timecardDay "deleted" event.
     *
     * @param  TimecardDay  $timecardDay
     * @return void
     */
    public function deleted(TimecardDay $timecardDay): void
    {
        $timecardDay->generateAction('delete');
    }
}
