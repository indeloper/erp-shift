<?php

namespace App\Traits;

use App\Models\Task;
use Carbon\Carbon;

trait TimeCalculator
{
    /**
     * This function calculate new time
     * for tasks considering working time
     *
     * @param  Carbon  $testDate  = null
     * @return Carbon $date
     */
    public function addHours(int $addHours, ?Carbon $testDate = null)
    {
        $date = $testDate ? $testDate->second(0)->micro(0) : now()->second(0)->micro(0);
        $workHoursSum = Task::UPPER_WORK_HOUR_LIMIT - Task::LOWER_WORK_HOUR_LIMIT;
        $extraDays = intval($addHours / $workHoursSum);
        $hours = intval($addHours % $workHoursSum);
        $date->addDays($extraDays);
        $date->addHours($hours);

        while ($this->isNotValidDate($date)) {
            if ($this->isMoreOrEqualToUpperWorkingHours($date)) {
                $extraHours = $date->hour - Task::UPPER_WORK_HOUR_LIMIT;
                $date->addDay()->hour(Task::LOWER_WORK_HOUR_LIMIT + $extraHours)->minute($date->minute);
            } elseif ($this->isLessThanLowerWorkingHours($date)) {
                $extraHours = Carbon::HOURS_PER_DAY - Task::UPPER_WORK_HOUR_LIMIT + $date->hour;
                $date->hour(Task::LOWER_WORK_HOUR_LIMIT + $extraHours)->minute($date->minute);
            }
        }

        if (! $date->isWeekday()) {
            $this->moveFromWeekends($date);
        }

        return $date;
    }

    /**
     * Function check date validity
     */
    public function isNotValidDate($date): bool
    {
        return ! $this->inWorkingHours($date) || $this->isLastHourWithMinutes($date);
    }

    /**
     * Function check that date in
     * time period 8-19
     */
    public function inWorkingHours($date): bool
    {
        return in_array($date->hour, range(Task::LOWER_WORK_HOUR_LIMIT, Task::UPPER_WORK_HOUR_LIMIT));
    }

    /**
     * Function check that date
     * more than 19:00
     */
    public function isLastHourWithMinutes($date): bool
    {
        return $date->hour == 19 and $date->minute != 0;
    }

    /**
     * Function return true
     * if date hour 19 or bigger
     */
    public function isMoreOrEqualToUpperWorkingHours($date): bool
    {
        return $date->hour >= Task::UPPER_WORK_HOUR_LIMIT;
    }

    /**
     * Function return true
     * if date hour less than 8
     */
    public function isLessThanLowerWorkingHours($date): bool
    {
        return $date->hour < Task::LOWER_WORK_HOUR_LIMIT;
    }

    /**
     * Function move date from
     * weekends
     */
    public function moveFromWeekends($date): void
    {
        $hour = $date->hour;
        $minute = $date->minute;
        $date->dayOfWeek == Carbon::SATURDAY ?
            $date->addWeek()->startOfWeek()->hour($hour)->minute($minute) :
            $date->addWeek()->weekDay(Carbon::MONDAY)->hour($hour)->minute($minute);
    }

    /**
     * This function calculate new time
     * for tasks considering working time,
     * but use days as addition
     *
     * @param  Carbon  $testDate  = null
     * @return Carbon $date
     */
    public function addDays(int $addDays, ?Carbon $testDate = null)
    {

        $date = $testDate ? $testDate->second(0)->micro(0) : now()->second(0)->micro(0);
        $date->addDays($addDays);

        while ($this->isNotValidDate($date)) {
            if ($this->isMoreOrEqualToUpperWorkingHours($date)) {
                $extraHours = $date->hour - Task::UPPER_WORK_HOUR_LIMIT;
                $date->addDay()->hour(Task::LOWER_WORK_HOUR_LIMIT + $extraHours)->minute($date->minute);
            } elseif ($this->isLessThanLowerWorkingHours($date)) {
                $extraHours = Carbon::HOURS_PER_DAY - Task::UPPER_WORK_HOUR_LIMIT + $date->hour;
                $date->hour(Task::LOWER_WORK_HOUR_LIMIT + $extraHours)->minute($date->minute);
            }
        }

        if (! $date->isWeekday()) {
            $this->moveFromWeekendsToClosestWorkingDay($date);
        }

        return $date;
    }

    /**
     * Function move date from
     * weekends to closest working day
     */
    public function moveFromWeekendsToClosestWorkingDay($date): void
    {
        $hour = $date->hour;
        $minute = $date->minute;
        if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
            $date->isoWeekday(Carbon::FRIDAY)->hour($hour)->minute($minute);
        }
    }
}
