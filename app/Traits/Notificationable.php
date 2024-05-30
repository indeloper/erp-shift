<?php

namespace App\Traits;

use App\Models\Notification\Notification;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Notificationable
{
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notificationable');
    }
}
