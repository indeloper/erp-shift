<?php

namespace App\Models;

use App\Models\MatAcc\MaterialAccountingOperation;
use Exception;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use morphos\Russian\FirstNamesInflection;
use morphos\Russian\LastNamesInflection;
use morphos\Russian\MiddleNamesInflection;
use App\Models\Notifications\{NotificationsForUsers, NotificationTypes, UserDisabledNotifications};
use App\Models\TechAcc\OurTechnicTicket;
use App\Models\Vacation\{
    ProjectResponsibleUserRedirectHistory,
    VacationsHistory
};
use App\Traits\{DevExtremeDataSourceLoadable, Logable, Messagable, Reviewable, TicketResponsibleUser};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use function morphos\Russian\inflectName;

class User extends Authenticatable
{
    use Notifiable, Reviewable, Messagable, TicketResponsibleUser, Logable, DevExtremeDataSourceLoadable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
        'department_id',
        'company',
        'is_deleted',
        'status',
        'first_name',
        'last_name',
        'patronymic',
        'birthday',
        'person_phone',
        'image',
        'gender',
        'INN'
    ];

    protected $table = 'users';

    public $role_codes = [
        0 => 'Любая',
        1 => 'Отв. за КП (сваи)',
        2 => 'Отв. за КП (шпунт)',
        3 => 'Отв. за ОР (сваи)',
        4 => 'Отв. за ОР (шпунт)',
        5 => 'РП (сваи)',
        6 => 'РП (шпунт)',
        7 => 'Отв. по договорной работе',
        8 => 'Отв. производитель работ (сваи)',
        9 => 'Отв. производитель работ (шпунт)',
    ];

    // role -> tasks_for_this_role[]
    public $role_tasks = [
        1 => [5, 6],
        2 => [5, 6],
        3 => [4],
        4 => [3],
        5 => [24],
        6 => [25],
        7 => [7, 9, 10, 12, 13],
        8 => [],
        9 => [],
    ];

    //array of department_id for one limitation mode
    public $limited_access = [
        0 => [14, 23, 31],
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $appends = [
        'full_name',
        'long_full_name',
        'card_route',
        'group_name',
        'company_name'
    ];

    public static $companies = [
        1 => 'ООО «СК ГОРОД»',
        2 => 'ООО «ГОРОД»',
        3 => 'ООО «СТРОЙМАСТЕР»',
        4 => 'ООО «РЕНТМАСТЕР»',
        5 => 'ООО «Вибродрилл Технология»',
        6 => 'ИП Исмагилов А.Д.',
        7 => 'ИП Исмагилов М.Д.',
    ];

    const TECH_TICKETS_GROUPS = [
        8, 13, 14, 15, 17, 19, 23, 27,
        31, 35, 37, 39, 40, 41, 42, 43,
        44, 45, 46, 47, 48
    ];

    const HARDCODED_PERSONS = [
        'SYSTEMGOD' => 1,
        'router' => 56,
        'certificateWorker' => 29,
        'CEO' => 6,
        'subCEO' => 7,
        'mainPTO' => 22
    ];

    const FILTERS = [
        'name' => 'name', // ФИО
        'birthday' => 'birthday', // День рождения
        'email' => 'email', // Почта
        'person_phone' => 'person_phone', // Личный телефон
        'work_phone' => 'work_phone', // Рабочий телефон
        'department_id' => 'department_id', // Департамент
        'group_id' => 'group_id', // Должность
        'company' => 'company', // Компания
        'project_object_id' => 'project_object_id', // Объект
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('email', function (Builder $builder) {
            $builder->whereNotNull('email');
        });
    }

    /**
     * Return users for given filter.
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeFilter(Builder $query, Request $request)
    {
        $filters = $request->filters ?? [];
        $values = $request->values ?? [];

        foreach ($filters as $key => $filter) {
            if (in_array($filter, self::FILTERS)) {
                if ($filter == self::FILTERS['birthday']) {
                    $explodedDate = explode('|', $values[$key]);
                    $from = $explodedDate[0];
                    $to = $explodedDate[1];
                    if ($from) {
                        $query->orWhere('birthday', '>=', $from);
                    }
                    if ($to) {
                        $query->orWhere('birthday', '<=', $to);
                    }
                } else if ($filter == 'name') {
                    $names = (array) $values[$key];
                    foreach ($names as $name) {
                        $query->orWhere('last_name', 'like', '%' . $name . '%')
                            ->orWhere('first_name', 'like', '%' . $name . '%')
                            ->orWhere('patronymic', 'like', '%' . $name . '%');
                    }
                } else if (in_array($filter, [self::FILTERS['person_phone'], self::FILTERS['work_phone']])) {
                    $phones = (array) $values[$key];
                    foreach ($phones as $phone) {
                        $query->orWhere($filter, 'like', '%' . $phone . '%');
                    }
                } else {
                    $query->whereIn($filter,(array) $values[$key]);
                }
            }
        }

        return $query;
    }

    public function scopeForTechTickets(Builder $query, ?string $q, ?array $group_ids)
    {
        $q = $q ?? false;

        $query->where(function($nested) {
            $nested->whereHas('user_permissions', function($perm) {
                return $perm->where('category', 13);
            })->orWhereHas('group.group_permissions', function($perm) {
                return $perm->where('category', 13);
            });
        });

        if ($q) {
            $groups = Group::where('name', $q)
                ->orWhere('name', 'like', '%' . $q . '%')
                ->pluck('id')
                ->toArray();

            $query->where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like', '%' . $q . '%');

            if (!empty($groups)) {
                $query->orWhereIn('group_id', [$groups]);
            }
        }

        return $query;
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_deleted', 0)->where('status', 1);
    }

    /**
     * Function find users with provided $user_ids
     * and some search parameters
     * @param Builder $query
     * @param string|null $q
     * @param array $user_ids
     * @return Builder
     */
    public function scopeForDefects(Builder $query, ?string $q, array $user_ids = [])
    {
        $q = $q ?? false;

        $query->whereIn('id', $user_ids);

        if ($q) {
            $query->where(function ($subquery) use ($q) {
                $subquery->orWhere('last_name', 'like', '%' . $q . '%')
                ->orWhere('first_name', 'like', '%' . $q . '%')
                ->orWhere('patronymic', 'like', '%' . $q . '%')
                ->orWhere(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like', '%' . $q . '%');
            });
        }

        return $query;
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d.m.Y H:i:s');
    }


    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    /**
     * Getter for user card route
     * @return string
     */
    public function getCardRouteAttribute()
    {
        return route('users::card', $this->id);
    }


    static function getAllUsers()
    {
        return User::where('users.is_deleted', 0)->select('users.*', 'users.department_id as dep_id', 'departments.name as dep_name', 'groups.name as group_name', 'groups.id as group_id')
            ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
            ->leftJoin('groups', 'groups.id', '=', 'users.group_id');
    }

    /**
     * Scope for users with telegram chat_id property
     * @param Builder $query
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeWithTelegramChatId(Builder $query)
    {
        return $query->whereNotNull('chat_id');
    }

    /**
     * Scope for users without telegram chat_id property
     * @param Builder $query
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeWithoutTelegramChatId(Builder $query)
    {
        return $query->whereNull('chat_id');
    }

    /**
     * Scope for users who have birthday today
     * @param Builder $query
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeWhoHaveBirthdayToday(Builder $query)
    {
        return $query->where('birthday','like', '%' . now()->format('d.m') . '%')
            ->where('status', '=', 1)
            ->where('is_deleted', '=', 0);
    }

    /**
     * Scope for users who have birthday next week
     * @param Builder $query
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeWhoHaveBirthdayNextWeek(Builder $query)
    {
        return $query->where('birthday','like', '%' . now()->addWeek()->format('d.m') . '%')
            ->where('status', '=', 1)
            ->where('is_deleted', '=', 0);
    }

    public function hasLimitMode($mode = 0)
    {
        return in_array($this->getAllGroupIds(), $this->limited_access[$mode]);
    }


    //maxon doesn't use it
    public function permissions()
    {
        return Permission::where('user_permissions.user_id', $this->id)
            ->leftJoin('user_permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->get();
    }


    //maxon uses that
    public function getAllPermissionsAttribute()
    {
        if (!$this->all_permissions_cache) {
            $all_permissions = $this->user_permissions;
            $all_permissions = $all_permissions->merge($this->group->group_permissions);

            foreach ($this->replaced_users as $replaced_user) {
                $all_permissions = $all_permissions->merge($replaced_user->all_permissions);
            }

            $this->all_permissions_cache = $all_permissions->unique();
        }

        return $this->all_permissions_cache;
    }


    public function user_permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id','permission_id');
    }


    public function getFullNameAttribute()
    {
        return $this->user_full_name;
    }


    public function getLongFullNameAttribute()
    {
        return trim($this->last_name . ' ' . $this->first_name . ($this->patronymic ? ' ' . $this->patronymic : ''));
    }


    public function getGroupNameAttribute()
    {
        return $this->group->name ?? 'Не указана';
    }


    public function getCompanyNameAttribute()
    {
        return self::$companies[$this->company] ?? 'Не указана';
    }

    public function isProjectManager()
    {
        return $this->isInGroup(...Group::PROJECT_MANAGERS);
    }


    public function isForeman() //прораб
    {
        return $this->isInGroup(...Group::FOREMEN);
    }


    /**
     * This function will return true if given user
     * can create only drafts of operations and cannot
     * create real operations of given $type
     * @param string $type
     * @return bool | Exception
     */
    public function isOperationDrafter(string $type)
    {
        if (! in_array($type, (new MaterialAccountingOperation())->eng_type_name)) return new Exception("Given Operation type doesn't exist");

        return boolval($this->can("mat_acc_{$type}_draft_create") and $this->cannot("mat_acc_{$type}_create"));
    }

    /**
     * This function will return true if given user
     * can create operations of given $type
     * @param string $type
     * @return bool | Exception
     */
    public function isOperationCreator(string $type)
    {
        if (! in_array($type, (new MaterialAccountingOperation())->eng_type_name)) return new Exception("Given Operation type doesn't exist");

        return boolval($this->can("mat_acc_{$type}_create"));
    }

    /**
     * Function return true if user can work with importance or projects
     * @return bool
     */
    public function canWorkWithImportance(): bool
    {
        return $this->can('update_project_importance');
    }


    public function technic_tickets()
    {
        return $this->belongsToMany(OurTechnicTicket::class, 'our_technic_ticket_user', 'user_id', 'tic_id')
            ->groupBy('id')
            ->withPivot(['type', 'deactivated_at'])
            ->as('ticket_responsible')
            ->withTimestamps();
    }

    public function group()
    {
        return $this->belongsTo(Group::class)->orderBy('id');
    }


    public function tasks()
    {
        return $this->hasMany(Task::class, 'responsible_user_id', 'id')->where('is_solved', 0);
    }


    public function allTasks()
    {
        return $this->hasMany(Task::class, 'responsible_user_id', 'id');
    }


    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    public function timeResponsibleProjects()
    {
        return $this->hasMany(Project::class, 'time_responsible_user_id', 'id');
    }

    public function last_vacation()
    {
        if ($this->in_vacation) {
            return $this->hasOne(VacationsHistory::class, 'vacation_user_id', 'id')
                ->orderBy('id', 'desc');
        }

        return $this->hasOne(VacationsHistory::class, 'vacation_user_id', 'id')->where('id', 0);

    }

    /**
     * Relation to user project roles
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectRoles()
    {
        return $this->hasMany(ProjectResponsibleUser::class, 'user_id', 'id');
    }


    public function hasPermission($ability)
    {
        return $this->is_su ? true : $this->all_permissions->contains('codename', $ability);
    }


    public function user_name()
    {
        return $this->last_name . ' ' . $this->first_name . ' ' . $this->patronymic;
    }


    /**
     * Checks if user is in group(s)
     * also takes group of replaced users
     *
     * @params int one or several group_id
     *
     * @return boolean
     *
     */
    public function isInGroup(...$groups_to_check)
    {
        return !empty(array_intersect($groups_to_check, $this->getAllGroupIds()));
    }


    public function getAllGroupIds()
    {
        $all_groups = [$this->group->id];

        foreach ($this->replaced_users as $replaced_user) {
            $all_groups = array_merge($all_groups, $replaced_user->getAllGroupIds());
        }

        return $all_groups;
    }


    public static function to_vacation($id, $vacation)
    {
        DB::beginTransaction();

        // update user
        User::where('id', $id)->update(['in_vacation' => 1]);

        // responsible_user logic
        $roles = ProjectResponsibleUser::where('user_id', $id)->get();
        $roles_move = ProjectResponsibleUser::moveResponsibleUser($roles, $id, $vacation->support_user_id, $vacation->id);

        // tasks logic
        $tasks = Task::where('responsible_user_id', $id)->where('is_solved', 0)->get();
        $tasks_move = Task::moveTasks($tasks, $id, $vacation->support_user_id, $vacation->id);

        // notifications block
        $vacation_user = User::find($id);

        // this update specially for hook
        $vacation->update(['is_actual' => 1]);

        DB::commit();

        return true;
    }


    public static function from_vacation($id, $vacation)
    {
        DB::beginTransaction();

        // update user
        User::where('id', $vacation->vacation_user_id)->update(['in_vacation' => 0]);

        // move tasks back
        $already_moved_tasks = TaskRedirect::where('vacation_id', $vacation->id)->get();
        $tasks = TaskRedirect::tasks($already_moved_tasks->pluck('task_id')->toArray());
        $tasks_move = Task::moveTasksBack($tasks, $vacation->support_user_id, $id, $vacation->id);

        // move roles back
        $already_moved_roles = ProjectResponsibleUserRedirectHistory::where('vacation_id', $vacation->id)->get();
        $roles = ProjectResponsibleUserRedirectHistory::roles($already_moved_roles->pluck('role_id')->toArray());
        $roles_move = ProjectResponsibleUser::moveResponsibleUserBack($roles, $vacation->support_user_id, $id, $vacation->id);

        // update vacation
        $vacation->update(['is_actual' => 0, 'return_date' => now()]);

        DB::commit();

        return true;
    }


    public function replaced_users()
    {
        return $this->belongsToMany(User::class, 'vacations_histories', 'support_user_id', 'vacation_user_id')
            ->wherePivot('is_actual', 1)
            ->wherePivot('change_authority', 1);
    }


    public function replacing_users()
    {
        return $this->belongsToMany(User::class, 'vacations_histories', 'vacation_user_id', 'support_user_id')
            ->wherePivot('is_actual', 1)
            ->wherePivot('change_authority', 1);
    }


    public static function remove_user($remove_user_id, $support_user_id)
    {
        DB::beginTransaction();

        // find user
        $user = User::withoutGlobalScope('email')->where('id', $remove_user_id)->firstOrFail();

        if ($user->is_deleted) {
            return redirect()->route('users::edit', $remove_user_id)->with('too_much_vacations', 'Сотрудник уже удалён');
        }

        // tasks logic
        $tasks = Task::where('responsible_user_id', $remove_user_id)->where('is_solved', 0)->get();
        // send 0 in vacation_id because this is not vacation :^)
        $tasks_move = Task::moveTasks($tasks, $remove_user_id, $support_user_id, 0, 'Удаление сотрудника');

        // responsible_user logic
        $roles = ProjectResponsibleUser::where('user_id', $remove_user_id)->get();
        // send 0 in vacation_id because this is not vacation :^)
        $roles_move = ProjectResponsibleUser::moveResponsibleUser($roles, $remove_user_id, $support_user_id, 0, 'Удаление сотрудника');

        // "remove" user
        $user->is_deleted = 1;
        $user->status = 0;
        // put support_user_id in public property of current user
        $user->role_codes = $support_user_id;
        $user->save();

        DB::commit();

        return true;
    }


    public function alwaysAllowedNotifications()
    {
        return NotificationTypes::getModel()->alwaysAllowedNotifications();
    }


    public function relatedNotifications()
    {
        return $this->hasMany(NotificationsForUsers::class, 'user_id', 'id');
    }


    public function disabledNotifications()
    {
        return $this->hasMany(UserDisabledNotifications::class, 'user_id', 'id');
    }


    public function disabledInSystemNotifications()
    {
        return $this->disabledNotifications->where('in_system', 0);
    }


    public function disabledInTelegramNotifications()
    {
        return $this->disabledNotifications->where('in_telegram', 0);
    }


    public function fullyDisabledNotifications()
    {
        return $this->disabledNotifications->where('in_telegram', 0)->where('in_system', 0);
    }


    public function checkIfNotifyDisabled(int $notificationType)
    {
        return in_array($notificationType, $this->fullyDisabledNotifications()->pluck('notification_id')->toArray());
    }


    public function checkIfNotifyDisabledInTelegram(int $notificationType)
    {
        return in_array($notificationType, $this->disabledInTelegramNotifications()->pluck('notification_id')->toArray());
    }

    public function checkIfNotifyNotDisabledInTelegram(int $notificationType)
    {
        return $this->checkIfNotifyDisabledInTelegram($notificationType) ? false : true;
    }


    public function allowedNotifications()
    {
        return array_unique(
            array_merge(
                $this->getAlwaysAllowedNotificationIdsToArray(),
                $this->getNotificationIdsFromGroupToArray(),
                $this->getNotificationIdsFromUserToArray(),
                $this->getNotificationIdsFromPermissionsToArray()
            )
        );
    }

    public function getAlwaysAllowedNotificationIdsToArray()
    {
        return $this->alwaysAllowedNotifications()->pluck('id')->toArray();
    }


    public function getNotificationIdsFromGroupToArray()
    {
        return $this->group->relatedNotifications->pluck('notification_id')->toArray();
    }


    public function getNotificationIdsFromUserToArray()
    {
        return $this->relatedNotifications->pluck('notification_id')->toArray();
    }


    public function getNotificationIdsFromPermissionsToArray()
    {
        $notifications_cache = [];

        foreach ($this->getAllPermissionsAttribute() as $permission) {
            foreach ($permission->relatedNotifications as $relation) {
                $notifications_cache[] = $relation->notification_id;
            }
        }

        return array_unique($notifications_cache);
    }

    /**
     * Check if user is time responsible user on project
     * or project responsible RP
     * @param int $projectId
     * @return bool
     */
    public function isProjectTimeResponsibleOrProjectResponsibleRP(int $projectId)
    {
        $project = Project::find($projectId);

        if (($project->timeResponsible ? $project->timeResponsible->id : 0) === $this->id) {
            return true;
        } else if (ProjectResponsibleUser::where('project_id', $project ? $project->id : 0)->whereIn('role', [8, 9])->exists()) {
            return true;
        }

        return false;
    }

    /**
     * @param string $format
     * F - Full firstname;
     * f - Fist letter of firstName;
     * L - Full lastname;
     * l - Fist letter of lastname;
     * P - Full patronymic;
     * p - Fist letter of patronymic;
     * @param $declension
     * @return array|string|string[]
     * @throws Exception
     * @example User::find($userId)->format('L f. p.', 'родительный'); // returns Иванов -> Иванова А. С.
     */
    public function format(string $format = 'LFP', $declension = null): string {
        $patronymicExcludes = ['Угли','угли', 'Оглы', 'оглы', 'Оглу', 'оглу'];

        $fullName = str_replace($patronymicExcludes, '', $this->long_full_name);

        if (!empty($declension)) {
            $fullName = inflectName($fullName, $declension, mb_strtolower($this->gender));
        }

        $lastName = explode(' ', $fullName)[0];
        $firstName = explode(' ', $fullName)[1];
        if (isset(explode(' ', $fullName)[2])) {
            $patronymic = explode(' ', $fullName)[2];
        } else {
            $patronymic = '';
        }

        $result = $format;

        if (mb_strpos($result, 'l') > 0){
            $lastName = mb_substr($lastName, 0, 1, 'UTF-8');
            $result = str_replace('l', $lastName, $result);
        } else {
            $result = str_replace('L', $lastName, $result);
        }

        if (mb_strpos($result, 'f') > 0){
            $firstName = mb_substr($firstName, 0, 1, 'UTF-8');
            $result = str_replace('f', $firstName, $result);
        } else {
            $result = str_replace('F', $firstName, $result);
        }

        if (!empty($patronymic)) {
            if (mb_strpos($result, 'p') > 0) {
                $patronymic = mb_substr($patronymic, 0, 1, 'UTF-8');
                $result = str_replace('p', $patronymic, $result);
            } else {
                $result = str_replace('P', $patronymic, $result);
            }
        } else {
            $result = str_replace('p', '', $result);
            $result = str_replace('P', '', $result);
        }

        return str_replace('. .', '.', $result);
    }

    public function getExternalUserUrl()
    {
        return $this->chat_id
            ? 'tg://user?id='.$this->chat_id
            : asset('/users/card').'/'.$this->id ?? null;
    }
}
