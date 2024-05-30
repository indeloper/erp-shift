<?php

namespace App\Models\CommercialOffer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommercialOfferRequest extends Model
{
    public $request_status = [
        0 => 'Не просмотрен',
        1 => 'Положительный',
        2 => 'Отрицательный',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(CommercialOfferRequestFile::class, 'request_id', 'id');
    }

    public function co(): HasOne
    {
        return $this->hasOne(CommercialOffer::class, 'id', 'commercial_offer_id');
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }
}
