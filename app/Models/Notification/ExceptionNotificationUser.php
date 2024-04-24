<?php

namespace App\Models\Notification;

use Illuminate\Database\Eloquent\Model;

class ExceptionNotificationUser extends Model
{
    protected $fillable = [
        'user_id',
        'notification_item_id',
        'channel'
    ];

    public $timestamps = false;
}
