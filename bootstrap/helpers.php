<?php

if (! function_exists('weekdayDate')) {
    function weekdayDate($date)
    {
        if ($date == false) {
            return $date;
        }
        $parsed_date = \Carbon\Carbon::parse($date);

        return $parsed_date->isoFormat('DD.MM.YYYY dd');
    }
}
