<?php

namespace App\Models\TechAcc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ProjectObject;
use App\Models\TechAcc\Vehicles\OurVehicles;
use App\Models\User;
use App\Services\TechAccounting\TechnicTicketService;
use App\Traits\Commentable;
use App\Traits\Notificationable;
use App\Traits\NotificationGenerator;
use App\Traits\RussianShortDates;
use App\Traits\Taskable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OurTechnicTicket extends Model
{
    use HasFactory;

    use Commentable;
    use Notificationable;
    use NotificationGenerator;
    use RussianShortDates;
    use SoftDeletes;
    use Taskable;

    protected $fillable = [
        'our_technic_id',
        'sending_object_id',
        'getting_object_id',
        'usage_days',
        'sending_from_date',
        'sending_to_date',
        'getting_from_date',
        'getting_to_date',
        'usage_from_date',
        'usage_to_date',
        'comment',
        'type',
        'status',
        'specialization',
    ];

    public $statuses = [
        1 => 'Новая заявка',
        2 => 'Ожидает назначения',
        3 => 'Отклонена',
        4 => 'Удержание',
        5 => 'Ожидает начала использования',
        6 => 'Перемещение',
        7 => 'Использование',
        8 => 'Завершена',
    ];

    public $types = [
        1 => 'Использование',
        2 => 'Перемещение',
        3 => 'Использование с перемещением',
    ];

    public $specializations = [
        1 => 'Шпунт',
        2 => 'Сваи',
        3 => 'Монолит',
    ];

    protected $appends = ['short_data', 'class_name', 'can_extension', 'human_specialization'];

    protected $casts = [
        'sending_from_date' => 'string',
        'sending_to_date' => 'string',
        'getting_from_date' => 'string',
        'getting_to_date' => 'string',
        'usage_from_date' => 'string',
        'usage_to_date' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ticket) {
            $date_columns = [
                'sending_from_date',
                'sending_to_date',
                'getting_from_date',
                'getting_to_date',
                'usage_from_date',
                'usage_to_date',
            ];
            foreach ($date_columns as $column) {
                if ($ticket->$column) {
                    $ticket->$column = Carbon::parse($ticket->$column);
                }
            }
            if (isset($ticket->getChanges()['our_technic_id'])) {
                $ticket->sending_object_id = $ticket->our_technic->start_location_id;
            }
            if (! $ticket->sending_object_id and $ticket->our_technic()->exists()) {
                $ticket->sending_object_id = $ticket->our_technic->start_location_id;
            }
        });

        static::addGlobalScope('orderByUpdated', function (Builder $builder) {
            $builder->orderByDesc('updated_at');
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'our_technic_ticket_user', 'tic_id')
            ->withPivot(['type', 'deactivated_at'])
            ->as('ticket_responsible')
            ->withTimestamps();
    }

    public function usersOrdered()
    {
        return $this->users()->orderBy('pivot_created_at');
    }

    public function getResponsibleType($human_type)
    {
        return $this->users()->ofType($human_type)->first() ?? new User();
    }

    public function reports()
    {
        return $this->hasMany(OurTechnicTicketReport::class)->orderByRaw("STR_TO_DATE(date,'%d.%m.%Y') desc");
    }

    public function our_technic()
    {
        return $this->belongsTo(OurTechnic::class);
    }

    public function getting_object()
    {
        return $this->belongsTo(ProjectObject::class, 'getting_object_id');
    }

    public function sending_object()
    {
        return $this->belongsTo(ProjectObject::class, 'sending_object_id');
    }

    public function vehicles()
    {
        return $this->belongsToMany(OurVehicles::class, 'our_technic_ticket_our_vehicle', 'our_technic_ticket_id', 'our_vehicle_id');
    }

    public function getShortDataAttribute()
    {
        $technic = $this->our_technic()->select(['model', 'brand'])->first();
        $short_names = [
            'model' => $technic->model,
            'brand' => $technic->brand,
            'resp_rp_name' => $this->users()->wherePivot('type', 1)->first()->full_name,
            'author_name' => $this->users()->wherePivot('type', 5)->first()->full_name,
            'process_resp_name' => $this->users()->wherePivot('type', 6)->first()->full_name ?? '-',
            'object_adress' => $this->getting_object->short_name ?? $this->getting_object->name,
            'status_name' => isset($this->statuses[$this->status]) ? $this->statuses[$this->status] : 'Отклонена',
            //'inventory_number' => $this->our_technic->inventory_number,
        ];

        return $short_names;
    }

    public function getHumanSpecializationAttribute()
    {
        return $this->specializations[$this->specialization] ?? 'Нет';
    }

    public function getShowButtonsAttribute()
    {
        $authed_user = auth()->user();
        $authed_users = $this->users()->where('id', $authed_user->id)->activeResp()->get();

        $authed_types = $authed_users->count() ? $authed_users->pluck('ticket_responsible.type') : collect([]);

        $curr_resps = (new TechnicTicketService())->ticket_status_responsible_user_map[$this->status] ?? [];
        if ($authed_types === false) {
            return false;
        }

        foreach ($curr_resps as $type) {
            if ($authed_user->isProjectManager()) {
                return true;
            }
            if ($type == 'logist' and $authed_user->main_logist_id === $authed_user->id) {
                return true;
            }
            $number_type = array_search($type, $authed_user->ticket_responsible_types);
            if ($authed_types->contains($number_type)) {
                return true;
            }
        }

        return false;
    }

    public function getActiveUsersAttribute()
    {
        return $this->users()->activeResp()->get();
    }

    public function close()
    {
        $this->update([
            'usage_to_date' => Carbon::now(),
            'status' => 8,
        ]);

        $this->generateOurTechnicTicketCloseNotifications($this);

        $this->tasks->each->solve_n_notify();

        return $this;
    }

    public function loadAllMissingRelations()
    {
        $this->append('show_buttons', 'active_users');

        return $this->loadMissing(['users', 'usersOrdered', 'our_technic', 'sending_object', 'getting_object', 'comments.files', 'reports', 'active_tasks.responsible_user', 'vehicles']);
    }

    public function getCanExtensionAttribute()
    {
        return ! $this->tasks()->where('status', 27)->whereIsSolved(0)->count();
    }

    public function getSendingTimestampsTextAttribute()
    {
        return 'с '.Carbon::parse($this->sending_from_date)->isoFormat('DD.MM.YYYY').' по '.Carbon::parse($this->sending_to_date)->isoFormat('DD.MM.YYYY');
    }

    public function getGettingTimestampsTextAttribute()
    {
        return 'с '.Carbon::parse($this->getting_from_date)->isoFormat('DD.MM.YYYY').' по '.Carbon::parse($this->getting_to_date)->isoFormat('DD.MM.YYYY');

    }

    public function scopeWithOutClosed($query)
    {
        return $query->whereNotIn('status', [3, 8]);
    }

    public function scopeFilter($query, array $request)
    {
        $query->with('users', 'reports', 'our_technic');

        foreach ($request as $param => $values) {
            if (in_array($param, ['id'])) {
                $query->where(function ($query) use ($param, $values) {
                    foreach ((array) $values as $item) {
                        $query->orWhere($param, 'like', '%'.$item.'%');
                    }
                });
            }
            if (in_array($param, ['status'])) {
                $query->where(function ($query) use ($param, $values) {
                    foreach ((array) $values as $item) {
                        $query->orWhere($param, 'like', '%'.array_flip($this->statuses)[$item].'%');
                    }
                });
            } elseif (in_array($param, ['sending_object_id', 'getting_object_id'])) {
                $query->where(function ($query) use ($param, $values) {
                    foreach ((array) $values as $item) {
                        $query->orWhere($param, $item);
                    }
                });
            } elseif (in_array($param, ['brand', 'model'])) {
                $query->whereHas('our_technic', function ($que) use ($param, $values) {
                    $que->where(function ($q) use ($param, $values) {
                        foreach ((array) $values as $item) {
                            $q->orWhere($param, 'like', '%'.$item.'%');
                        }
                    });

                });
            } elseif (in_array($param, ['page', 'ticket_id', 'show_active'])) {
            } elseif (in_array($param, ['resp_rp_user_id', 'usage_resp_user_id', /*'logist',*/ 'request_resp_user_id', 'recipient_user_id', 'author_user_id'])) {
                $query->whereHas('users', function ($q) use ($param, $values) {
                    $q->where('type', array_flip(User::getModel()->ticket_responsible_types)[$param]);
                    $q->whereIn('user_id', (array) $values);
                });
            } elseif ($param == 'search') {
                $query->where(function ($q) use ($values) {
                    $q->orWhere('id', 'like', '%'.$values.'%');
                    if (isset(array_flip($this->statuses)[$values])) {
                        $q->orWhere('status', 'like', '%'.array_flip($this->statuses)[$values].'%');
                    }
                    $q->orWhereHas('sending_object', function ($que) use ($values) {
                        $que->where(function ($query) use ($values) {
                            $query->orWhere('address', 'like', '%'.$values.'%');
                            $query->orWhere('name', 'like', '%'.$values.'%');
                        });
                    });
                    $q->orWhereHas('getting_object', function ($que) use ($values) {
                        $que->where(function ($query) use ($values) {
                            $query->orWhere('address', 'like', '%'.$values.'%');
                            $query->orWhere('name', 'like', '%'.$values.'%');
                        });
                    });

                    $q->orWhereHas('our_technic', function ($que) use ($values) {
                        $que->where(function ($q) use ($values) {
                            $q->orWhere('brand', 'like', '%'.$values.'%');
                            $q->orWhere('model', 'like', '%'.$values.'%');
                        });
                    });
                    $q->orWhereHas('users', function ($que) use ($values) {
                        $que->where(function ($q) use ($values) {
                            $q->orWhere('last_name', 'like', '%'.$values.'%');
                            $q->orWhere('first_name', 'like', '%'.$values.'%');
                        });
                    });
                });

            }
        }

        if (! isset($request['show_active'])) {
            $query->withOutClosed();
        }

        return $query;
    }

    /**
     * Return all tickets if user have permission
     * and only related if not
     *
     * @return Builder
     */
    public function scopePermissionCheck(Builder $query)
    {
        $check = boolval(auth()->user()->hasPermission('tech_acc_our_technic_tickets_see'));

        if (! $check) {
            $query->whereHas('users', function (Builder $q) {
                $q->whereId(auth()->id());
            });
        }

        return $query;
    }
}
