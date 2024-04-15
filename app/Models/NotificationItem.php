<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
