<?php

namespace App\Models\Notifications;

use Illuminate\Database\Eloquent\Model;

class TgNotificationUrl extends Model
{
    protected $fillable = ['target_url', 'encoded_url', 'notification_id'];
}
