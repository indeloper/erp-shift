<?php

use App\Domain\DTO\Notification\NotificationData;
use App\Jobs\Notification\NotificationJob;

if (! function_exists('weekdayDate')) {
    function weekdayDate ($date) {
        if ($date == false) {
            return $date;
        }
        $parsed_date = \Carbon\Carbon::parse($date);
        return $parsed_date->isoFormat('DD.MM.YYYY dd');
    }
}

if (!function_exists('dispatchNotify')) {
    function dispatchNotify(
        int $userId,
        string $name,
        ?string $description,
        int $type,
        array $data = []
    )
    {
        NotificationJob::dispatchNow(
            new NotificationData(
                $userId,
                $name,
                $description,
                $type,
                $data
            )
        );
    }
}

