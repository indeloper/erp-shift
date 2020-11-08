<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationsForUsers extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'notification_id',
        'user_id',
    ];
}
