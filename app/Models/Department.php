<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name'];

    const DEPARTMENTS = [
        // administration department
        1 => 'Администрация',
        2 => 'Административно-хозяйственный отдел',
        3 => 'Бухгалтерия',
        4 => 'Дирекция',
        5 => 'Отдел персонала',
        6 => 'Финансовый отдел',
        // construction department
        7 => 'Общестроительное направление',
        8 => 'ОТМС и логистики',
        9 => 'Лаборатория неразрушающего контроля',
        10 => 'Свайное направление',
        11 => 'Шпунтовое направление',
        12 => 'Склад',
        13 => 'УМиТ',
        // technical department
        14 => 'Коммерческий отдел',
        15 => 'Проектный отдел',
        16 => 'ПТО',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class)->where('status', 1)->where('is_deleted', 0)->where('id', '!=', 1);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function permission_ids($groups)
    {
        $perm = [];
        foreach ($groups as $group) {
            foreach ($group->group_permissions as $permission) {
                $perm[$permission->id] ?? $perm[$permission->id] = 0;
                $perm[$permission->id] += 1;
            }
        }

        $perm = array_filter($perm, function ($v, $k) use ($groups) {
            return $v == $groups->count();
        }, ARRAY_FILTER_USE_BOTH);

        return array_keys($perm);
    }
}
