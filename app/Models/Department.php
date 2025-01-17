<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property-read Collection<int, Group> $groups
 * @property-read int|null $groups_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static Builder|Department newModelQuery()
 * @method static Builder|Department newQuery()
 * @method static Builder|Department query()
 * @method static Builder|Department whereCreatedAt($value)
 * @method static Builder|Department whereId($value)
 * @method static Builder|Department whereName($value)
 * @method static Builder|Department whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Department extends Model
{
    use HasFactory;

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
