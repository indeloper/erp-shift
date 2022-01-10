<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PayAndHold extends Model
{
    protected $fillable = ['name', 'short_name', 'type'];

    public function scopePayments(Builder $q)
    {
        return $q->where('type', 1);
    }

    public function scopeHolds(Builder $q)
    {
        return $q->where('type', 2);
    }
}
