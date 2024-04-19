<?php

namespace App\Models\Notification;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NotificationItem extends Model
{
    protected $fillable = [
        'type',
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

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
