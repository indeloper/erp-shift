<?php

namespace App\Models;

use App\Models\CommercialOffer\CommercialOfferRequest;
use App\Models\WorkVolume\WorkVolumeRequest;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

    public $additional_info = [];

    public static $status_names = [
        '1' => 'Создание проекта',
        '2' => 'Ссылка на событие',
        '3' => 'Ссылка на заявку ОР',
        '4' => 'Ссылка на заявку КП',
        '5' => 'Ссылка на карту контрагента',
        '6' => 'Ссылка на карту подтверждения перемещения', //when 'fact to' it not equal to 'fact from' notification for 'Исмагилов Александр'
        '7' => 'Ссылка на карту операции', // for most part of MatAcc notifications
        '8' => 'Ссылка на согласование заявки', //please, remove this after friday
        '9' => 'Ссылка на список задач пользователя',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function wv_request()
    {
        return $this->belongsTo(WorkVolumeRequest::class, 'target_id', 'id');
    }

    public function co_request()
    {
        return $this->belongsTo(CommercialOfferRequest::class, 'target_id', 'id');
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notificationable()
    {
        return $this->morphTo();
    }
}
