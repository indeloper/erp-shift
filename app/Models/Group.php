<?php

namespace App\Models;

use App\Models\Notifications\NotificationsForGroups;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'department_id'];

    const GROUPS = [
        // administration
        // administration department
        1 => 'Специалист по управленческому учёту',
        57 => 'Заместитель генерального директора',
        // АХО department
        2 => 'Уборщица',
        //  Бухгалтерия department
        3 => 'Бухгалтер',
        4 => 'Главный бухгалтер',
        //  Дирекция department
        5 => 'Генеральный директор',
        6 => 'Заместитель генерального директора',
        7 => 'Секретарь руководителя',
        8 => 'Главный инженер',
        9 => 'Архивариус',
        // Отдел персонала department
        10 => 'Менеджер по персоналу',
        11 => 'Инженер по охране труда',
        // Финансовый отдел department
        12 => 'Финансовый директор',
        // construction
        // Общестроительный department
        13 => 'Руководитель проектов (общестроительное направление)',
        14 => 'Производитель работ (общестроительное направление)',
        55 => 'Геодезист (общестроительное направление)',
        // ОТМС и логистики department
        15 => 'Экономист по материально-техническому снабжению',
        16 => 'Агент по снабжению',
        17 => 'Специалист по логистике',
        // Лаборатория неразрушающего контроля department
        18 => 'Начальник лаборатории неразрушающего контроля',
        // Свайное направление department
        19 => 'Руководитель проектов (свайное направление)',
        20 => 'Электрогазосварщик (свайное направление)',
        21 => 'Электросварщик (свайное направление)',
        22 => 'Машинист крана (свайное направление)',
        23 => 'Производитель работ (свайное направление)',
        24 => 'Стропальщик (свайное направление)',
        25 => 'Машинистр копра',
        26 => 'Геодезист (свайное направление)',
        58 => 'Главный инженер (свайное направление)',
        // Шпунтовое направление department
        27 => 'Руководитель проектов (шпунтовое направление)',
        28 => 'Электрогазосварщик (шпунтовое направление)',
        29 => 'Электросварщик (шпунтовое направление)',
        30 => 'Машинист крана (шпунтовое направление)',
        31 => 'Производитель работ (шпунтовое направление)',
        32 => 'Стропальщик (шпунтовое направление)',
        33 => 'Копровщик',
        34 => 'Геодезист (шпунтовое направление)',
        35 => 'Мастер строительно-монтажных работ',
        36 => 'Начальник участка',
        37 => 'Техник',
        38 => 'Подсобный рабочий',
        56 => 'Машинистр экскаватора',
        // Склад department
        39 => 'Электрогазосварщик (склад)',
        40 => 'Электросварщик (склад)',
        41 => 'Машинист крана (склад)',
        42 => 'Стропальщик (склад)',
        43 => 'Заведующий складом',
        44 => 'Кладовщик',
        45 => 'Начальник производства',
        // УМиТ department
        46 => 'Механик',
        47 => 'Главный механик',
        48 => 'Электрослесарь по ремонту электрооборудования',
        // technical
        // Коммерческий отдел department
        49 => 'Юрист',
        50 => 'Директор по развитию',
        // Проектный отдел department
        51 => 'Инженер-проектировщик',
        // ПТО department
        52 => 'Инженер ПТО',
        53 => 'Начальник ПТО',
        54 => 'Экономист по договорной и претензионной работе',
    ];

    const FOREMEN = [14, 23, 31];
    const PROJECT_MANAGERS = [8, 13, 19, 27, 58];
    const LOGIST = [15, 16, 17];
    const MECHANICS = [46, 47];

    public function permissions()
    {
        return Permission::where('group_permissions.group_id', $this->id)
            ->leftJoin('group_permissions', 'group_permissions.permission_id','=','permissions.id')
            ->get();
    }

    public function getUsers()
    {
        $all_users = $this->users;
        $working_users = collect([]);

        foreach ($all_users as $user) {
            if ($user->in_vacation) {
                $working_users->push($user->replacing_users->first());
            } else {
                $working_users->push($user);
            }
        }

        return $working_users;
    }

    public function users()
    {
        return $this->hasMany(User::class)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->where('id', '!=', 1);
    }

    public function group_permissions()
    {
        return $this->belongsToMany(Permission::class, 'group_permissions', 'group_id', 'permission_id');
    }

    public function relatedNotifications()
    {
        return $this->hasMany(NotificationsForGroups::class, 'group_id', 'id');
    }
}
