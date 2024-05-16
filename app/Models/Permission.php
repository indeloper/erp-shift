<?php

namespace App\Models;

use App\Models\Notifications\NotificationsForPermissions;
use App\Traits\DevExtremeDataSourceLoadable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use DevExtremeDataSourceLoadable;

    protected $fillable = ['name', 'codename', 'category'];

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
        19 => 'Модуль "Охрана труда"',
        20 => 'Модуль "Документооборот: Площадка ⇆ Офис"',
    ];

    protected $appends = ['label', 'key'];

    public function getLabelAttribute()
    {
        return $this->name;
    }

    public function getKeyAttribute()
    {
        return $this->id;
    }

    public function relatedNotifications(): HasMany
    {
        return $this->hasMany(NotificationsForPermissions::class, 'permission', 'codename');
    }

    public function getUsersIdsByCodename($codename = null)
    {
        if (! $codename) {
            return [];
        }

        $permissionId = Permission::where('codename', $codename)->firstOrFail()->id;
        $relatedUsersIdsArr = UserPermission::where('permission_id', $permissionId)->pluck('user_id')->toArray();
        $relatedGroupsIdsArr = GroupPermission::where('permission_id', $permissionId)->pluck('group_id')->toArray();

        $permissionUsers = User::query()
            ->whereIn('group_id', $relatedGroupsIdsArr)
            ->orWhereIn('id', $relatedUsersIdsArr)
            ->active()
            ->pluck('id')
            ->toArray();

        return $permissionUsers;
    }

    public function scopeUsersIdsByCodename(Builder $query, $codename = null)
    {
        if (! $codename) {
            return [];
        }

        return $this->getUsersIdsByCodename($codename);
    }
}
