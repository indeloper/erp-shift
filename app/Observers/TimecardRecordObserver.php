<?php

namespace App\Observers;

use App\Models\HumanResources\TimecardRecord;

class TimecardRecordObserver
{
    /**
     * Handle the timecardRecord "updated" event.
     *
     * @param  TimecardRecord  $timecardRecord
     * @return void
     */
    public function updated(TimecardRecord $timecardRecord): void
    {
        $timecardRecord->generateAction('update');
    }

    /**
     * Handle the timecardRecord "created" event.
     *
     * @param  TimecardRecord  $timecardRecord
     * @return void
     */
    public function created(TimecardRecord $timecardRecord): void
    {
        $timecardRecord->generateAction();
    }

    /**
     * Handle the timecardRecord "deleted" event.
     *
     * @param  TimecardRecord  $timecardRecord
     * @return void
     */
    public function deleted(TimecardRecord $timecardRecord): void
    {
        $timecardRecord->generateAction('delete');
    }
}
