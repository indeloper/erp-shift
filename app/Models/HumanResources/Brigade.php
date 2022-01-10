<?php

namespace App\Models\HumanResources;

use App\Models\User;
use App\Services\HumanResources\TimecardService;
use App\Traits\{Appointmentable, HasAuthor, Logable, Notificationable, NotificationGenerator};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Brigade extends Model
{
    use SoftDeletes, Logable, HasAuthor, Notificationable, NotificationGenerator, Appointmentable;

    protected $fillable = ['number', 'direction', 'foreman_id', 'user_id'];

    protected $appends = ['foreman_name', 'name'];

    const DIRECTIONS = [
        1 => 'Шпунт',
        2 => 'Монтаж',
        3 => 'Шпунт/монтаж',
    ];

    const FILTERS = [
        'number' => 'number', // Номер бригады
        'direction' => 'direction', // Направления
        'foreman_id' => 'foreman_id', // Бригадиры
    ];

    // Local Scopes
    /**
     * Return brigades for given filter.
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
                $query->whereIn($filter,(array) $values[$key]);
            }
        }

        return $query;
    }

    /**
     * Relation to brigade foreman
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function foreman()
    {
        return $this->hasOne(User::class, 'id', 'foreman_id');
    }

    /**
     * Relation for brigade users
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'brigade_id', 'id');
    }

    /**
     * Getter for foreman full name
     * @return string
     */
    public function getForemanNameAttribute()
    {
        return $this->foreman ? $this->foreman->full_name : 'Не указан';
    }

    /**
     * Getter for brigade name
     * @return string
     */
    public function getNameAttribute()
    {
        return "Бригада номер {$this->number}";
    }

    /**
     * This function remove brigade assignment
     * from new foreman depends on foreman last position
     * @param Request $request
     */
    public function checkForemanStatus(Request $request)
    {
        if ($request->has('skip_other_brigade_check')) {
            $newForeman = User::find($request->foreman_id);
            $newForeman->update(['brigade_id' => null]);
        }

        if ($request->has('skip_other_brigade_foreman_check')) {
            $oldForemanBrigade = Brigade::where('foreman_id', $request->foreman_id)->where('id', '!=', $this->id);
            $oldForemanBrigade->update(['foreman_id' => null]);
        }
    }


    /**
     * This function can attach brigade
     * users or detach them, make logs
     * and send notifications
     * @param array $request
     */
    public function updateUsers(array $request)
    {
        $usersToAttach = $request['user_ids'] ?? [];
        $usersToDetach = $request['deleted_user_ids'] ?? [];

        $this->attachBrigadeUsers($usersToAttach);
        $this->detachBrigadeUsers($usersToDetach);
        $this->generateAction('update_users');
        $this->generateBrigadeUsersUpdateNotification();

        return $this->users;
    }

    /**
     * This function attach users to brigade
     * @param array $usersToAttach
     */
    public function attachBrigadeUsers(array $usersToAttach)
    {
        $users = User::whereIn('id', $usersToAttach)->get();
        foreach ($users as $user) {
            (new TimecardService())->fixUserTimecard($user);
            $this->users()->save($user);
        }
    }

    /**
     * This function detach users from brigade
     * @param array $usersToDetach
     */
    public function detachBrigadeUsers(array $usersToDetach)
    {
        User::whereIn('id', $usersToDetach)->get()->each(function ($user) {
            $user->update(['brigade_id' => null]);
        });
    }
}
