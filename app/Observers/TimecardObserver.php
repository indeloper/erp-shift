<?php

namespace App\Observers;

use App\Models\HumanResources\Timecard;

class TimecardObserver
{
    /**
     * Handle the timecard "updated" event.
     *
     * @param  Timecard  $timecard
     * @return void
     */
    public function updated(Timecard $timecard): void
    {
        if (array_key_exists('is_opened', $timecard->getChanges()) || array_key_exists('ktu', $timecard->getChanges())) {
            $timecard->generateAction('update');
        }
    }

    /**
     * Handle the timecard "created" event.
     *
     * @param  Timecard  $timecard
     * @return void
     */
    public function created(Timecard $timecard): void
    {
        // If we have previous month timecard
        $prev_month = $timecard->month == 1 ? 12 : $timecard->month - 1;
        $prev_year = $timecard->month == 1 ? $timecard->year - 1 : $timecard->year;
        $previousMonthTimecard = Timecard::where('user_id', $timecard->user_id)->where('month', $prev_month)->where('year', $prev_year)->first();
        if ($previousMonthTimecard) {
            // And previous timecard have prolonged compensations
            if ($prolongedCompensations = $previousMonthTimecard->compensations()->where('prolonged', 1)->get()) {
                // Then we should copy this compensations to new month
                $timecard->additions()->saveMany($prolongedCompensations);
            }
        }
        // Also we must create timecard days for all days in month
        $timecard->createMonthDays();
    }

    /**
     * Handle the timecard "creating" event.
     *
     * @param  Timecard  $timecard
     * @return void|bool
     */
    public function creating(Timecard $timecard)
    {
        // If we have any timecards for same user and same month
        $year = $timecard->year ?? now()->year;
        $timecard->year = $year;
        if (Timecard::where('user_id', $timecard->user_id)->where('month', $timecard->month)->where('created_at', 'like', "%{$year}%")->count()) {
            // We should abort doubled timecard creation
            return false;
        }
    }
}
