<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDisabledNotifications extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'notification_id',
        'in_telegram',
        'in_system',
    ];
}
