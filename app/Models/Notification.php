<?php

namespace App\Models;

use App\Models\CommercialOffer\CommercialOfferRequest;
use App\Models\Contractors\Contractor;
use App\Models\WorkVolume\WorkVolumeRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'user_id',
        'contractor_id',
        'project_id',
        'object_id',
        'department_id',
        'group_id',
        'is_seen',
        'is_showing',
        'type',
        'task_id',
        'voice_url',
        'created_at',
        'updated_at',
        'is_deleted',
        'target_id',
        'notificationable_type',
        'notificationable_id',
    ];

    protected $casts = [
        'is_deleted' => 'bool',
        'is_seen' => 'bool'
    ];

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

    public function object(): BelongsTo
    {
        return $this->belongsTo(ProjectObject::class, 'object_id', 'id');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
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
