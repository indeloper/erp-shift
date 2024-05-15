<?php

namespace App\Models\Vacation;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class VacationsHistory extends Model
{
    protected $fillable = ['vacation_user_id', 'support_user_id', 'from_date', 'by_date', 'return_date', 'is_actual', 'change_authority'];

    // WDIM - what does it mean
    public $WDIM_is_actual = [
        0 => 'Отпуск не актуален, пользователь уже не в отпуске',
        1 => 'Отпуск актуален, пользователь уже в отпуске',
    ];

    public $WDIM_change_authority = [
        0 => 'Обычный отпуск, должности не меняются', // default value
        1 => 'Для отпуска было указано, что заместитель получает полномочия (должность) сотрудника',
    ];

    public function user_vacation_status()
    {
        return User::find($this->vacation_user_id)->in_vacation;
    }

    public function vacation_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vacation_user_id', 'id');
    }

    public function support_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'support_user_id', 'id');
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getFromDateAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y');
    }

    public function getByDateAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y');
    }
}
