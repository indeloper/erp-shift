<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationsForPermissions extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'notification_id',
        'permission',
    ];
}
