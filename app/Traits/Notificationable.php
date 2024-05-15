<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Notification\Notification;

trait Notificationable
{
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notificationable');
    }
}
