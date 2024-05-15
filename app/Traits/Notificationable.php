<?php

namespace App\Traits;

use App\Models\Notification\Notification;

trait Notificationable
{
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notificationable');
    }
}
