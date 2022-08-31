<?php

namespace App\Models;

use App\Models\Notifications\NotificationsForPermissions;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'codename'];

    public $categories = [
        1 => 'Модуль "Задачи"',
        2 => 'Модуль "Проекты"',
        3 => 'Модуль "Контрагенты"',
        4 => 'Модуль "Объекты"',
        5 => 'Модуль "Материалы"',
        6 => 'Модуль "Работы"',
        7 => 'Модуль "Материальный учёт"',
        8 => 'Модуль "Проектная документация"',
        9 => 'Модуль "Коммерческих предложений"',
        10 => 'Модуль "Договоры"',
        11 => 'Модуль "Объёмы работ"',
        12 => 'Модуль "Сотрудники"',
        13 => 'Модуль "Техника"',
        14 => 'Модуль "Транспортные средства"',
        15 => 'Модуль "Дефекты"',
        16 => 'Модуль "Заявки на технику"',
        17 => 'Модуль "Топливные ёмкости"',
        18 => 'Модуль "Учёт человеческих ресурсов"',
        19 => 'Модуль "Охрана труда"'
    ];

    protected $appends = ['label', 'key'];

    function getLabelAttribute()
    {
        return $this->name;
    }

    function getKeyAttribute()
    {
        return $this->id;
    }

    public function relatedNotifications()
    {
        return $this->hasMany(NotificationsForPermissions::class, 'permission', 'codename');
    }
}
