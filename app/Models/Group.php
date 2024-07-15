<?php

namespace App\Models;

use App\Models\Notifications\NotificationsForGroups;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property int $department_id
 * @property-read Collection<int, Permission> $group_permissions
 * @property-read int|null $group_permissions_count
 * @property-read Collection<int, NotificationsForGroups> $relatedNotifications
 * @property-read int|null $related_notifications_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static Builder|Group newModelQuery()
 * @method static Builder|Group newQuery()
 * @method static Builder|Group query()
 * @method static Builder|Group whereCreatedAt($value)
 * @method static Builder|Group whereDepartmentId($value)
 * @method static Builder|Group whereId($value)
 * @method static Builder|Group whereName($value)
 * @method static Builder|Group whereUpdatedAt($value)
 * @property-read Department $department
 * @method static GroupFactory factory($count = null, $state = [])
 * @mixin Eloquent
 */
class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'department_id'];

    const FOREMEN = [14, 23, 31];

    const PROJECT_MANAGERS = [8, 13, 19, 27, 58];

    const LOGIST = [15, 16, 17];

    const MECHANICS = [46, 47];

    const PTO = [52, 53, 62];

    public function permissions()
    {
        return Permission::where('group_permissions.group_id', $this->id)
            ->leftJoin('group_permissions', 'group_permissions.permission_id', '=', 'permissions.id')
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

    public function users(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->where('id', '!=', 1);
    }

    public function group_permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'group_permissions', 'group_id', 'permission_id');
    }

    public function relatedNotifications(): HasMany
    {
        return $this->hasMany(NotificationsForGroups::class, 'group_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);

    }
}
