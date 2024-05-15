<?php

namespace App\Models\Notification;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NotificationItem extends Model
{
    protected $fillable = [
        'class',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'bool',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function exceptions(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'exception_notification_users', 'notification_item_id', 'user_id')
            ->withPivot('channel');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
