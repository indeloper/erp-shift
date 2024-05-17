<?php

namespace App\Models\TechAcc\Defects;

use App\Models\TechAcc\FuelTank\FuelTank;
use App\Models\TechAcc\OurTechnic;
use App\Models\User;
use App\Traits\Commentable;
use App\Traits\Documentable;
use App\Traits\Notificationable;
use App\Traits\Taskable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Defects extends Model
{
    use Commentable, Documentable, Notificationable, SoftDeletes, Taskable;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'defectable_id',
        'defectable_type',
        'description',
        'status',
        'responsible_user_id',
        'repair_start_date',
        'repair_end_date',
    ];

    protected $with = ['author', 'defectable', 'documents', 'photos', 'videos', 'responsible_user', 'comments'];

    protected $appends = ['status_name', 'repair_start', 'repair_end', 'contractor', 'created_at_formatted', 'class_name', 'author_name', 'responsible_user_name'];

    protected $casts = [
        'repair_start_date' => 'datetime',
        'repair_end_date' => 'datetime',
        'status' => 'integer',
    ];

    protected $hidden = ['contractor'];

    const STATUSES = [
        1 => 'Новая заявка',
        2 => 'Диагностика',
        3 => 'В работе',
        4 => 'Завершена',
        5 => 'Отклонена',
        6 => 'Удалена',
    ];

    const NEW = 1;

    const DIAGNOSTICS = 2;

    const IN_WORK = 3;

    const CLOSED = 4;

    const DECLINED = 5;

    const DELETED = 6;

    const USUALLY_SHOWING = [
        self::NEW,
        self::DIAGNOSTICS,
        self::IN_WORK,
    ];

    const USUALLY_HIDING = [
        self::CLOSED,
        self::DECLINED,
        self::DELETED,
    ];

    const DEFECTABLE_TYPE = [
        1 => OurTechnic::class,
        2 => FuelTank::class,
    ];

    const DATE_FORMAT = 'd.m.Y H:i:s';

    const ADDITIONAL_DATE_FORMAT = 'd.m.Y H:i';

    const REPAIR_DATE_FORMAT = 'd.m.Y';

    const FILTERS = [
        'brand' => 'brand', // Марка техники
        'model' => 'model', // Модель техники
        'tank_number' => 'tank_number', // Номер топливной ёмкости
        'status' => 'status', // Статус
        'user_id' => 'user_id', // Автор
        'responsible_user_id' => 'responsible_user_id', // Исполнитель
        'repair_start_date' => 'repair_start_date', // Начало ремонта
        'repair_end_date' => 'repair_end_date', // Окончание ремонта
        'defectable' => 'defectable', // Техническое устройство
        'inventory_number' => 'inventory_number', // Инвентарный номер техники
        'owner' => 'owner', // Юридическое лицо
    ];

    const DEFECT_FILTERS = [
        self::FILTERS['status'],
        self::FILTERS['user_id'],
        self::FILTERS['responsible_user_id'],
        self::FILTERS['repair_start_date'],
        self::FILTERS['repair_end_date'],
        self::FILTERS['defectable'],
    ];

    const DATE_FILTERS = [
        self::FILTERS['repair_start_date'],
        self::FILTERS['repair_end_date'],
    ];

    const TECHNIC_FILTERS = [
        self::FILTERS['brand'],
        self::FILTERS['model'],
        self::FILTERS['inventory_number'],
        self::FILTERS['owner'],
    ];

    const FUEL_TANK_FILTERS = [
        self::FILTERS['tank_number'],
    ];

    // Local Scopes
    /**
     * Return defects that in IN_WORK status and will expire soon.
     *
     * @param  int  $userId
     */
    public function scopeSoonExpire(Builder $query): Builder
    {
        return $query->whereStatus(self::IN_WORK)->whereNotNull('repair_end_date')->where(function ($q) {
            $q->whereDate('repair_end_date', '<=', now());
            $q->whereDate('repair_end_date', '>=', now()->subDay());
        });
    }

    /**
     * Return defects for given filter.
     */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        $filters = $request->filters ?? [];
        $values = $request->values ?? [];

        $show_active_key = array_search('show_active', $filters);
        $query->whereIn('status', $show_active_key !== false ? array_keys(self::STATUSES) : self::USUALLY_SHOWING);

        foreach ($filters as $key => $filter) {
            if (in_array($filter, self::DATE_FILTERS)) {
                $date = Carbon::createFromFormat('d.m.Y', $values[$key])->toDateString();
                $query->whereDate($filter, $filter == 'repair_start_date' ? '>=' : '<=', $date);
            } elseif (in_array($filter, self::TECHNIC_FILTERS)) {
                $query->whereHasMorph(
                    'defectable',
                    OurTechnic::class,
                    function (Builder $q) use ($filter, $values, $key) {
                        $q->where(function ($qq) use ($filter, $key, $values) {
                            $filter_values = (array) $values[$key];
                            foreach ($filter_values as $value) {
                                $qq->orWhere($filter, 'like', '%'.$value.'%');
                            }
                        });
                    }
                );
            } elseif (in_array($filter, self::FUEL_TANK_FILTERS)) {
                $query->whereHasMorph(
                    'defectable',
                    FuelTank::class,
                    function (Builder $q) use ($filter, $values, $key) {
                        $q->where(function ($qq) use ($filter, $key, $values) {
                            $filter_values = (array) $values[$key];
                            foreach ($filter_values as $value) {
                                $qq->orWhere($filter, 'like', '%'.$value.'%');
                            }
                        });
                    }
                );
            } elseif ($filter == 'defectable') {
                $filter_values = (array) $values[$key];
                $technic_ids = [];
                $fuel_ids = [];
                foreach ($filter_values as $value) {
                    [$defectable_id, $defectable_type] = explode('|', $value);
                    $type = $defectable_type == 1 ? 'technic_ids' : 'fuel_ids';
                    array_push($$type, $defectable_id);
                }

                $query->where(function ($q) use ($technic_ids, $fuel_ids) {
                    $q->whereHasMorph(
                        'defectable',
                        FuelTank::class,
                        function (Builder $q) use ($fuel_ids) {
                            $q->whereIn('id', $fuel_ids);
                        }
                    )->orWhereHasMorph(
                        'defectable',
                        OurTechnic::class,
                        function (Builder $q) use ($technic_ids) {
                            $q->whereIn('id', $technic_ids);
                        }
                    );
                });
            } elseif ($filter == 'status') {
                $search = array_map('mb_strtolower', (array) $values[$key]);
                $result = [];
                foreach ($search as $value) {
                    $search_result = array_filter(self::STATUSES, function ($item) use ($value) {
                        return stristr(mb_strtolower($item), $value);
                    });

                    foreach ($search_result as $status_key => $val) {
                        $result[] = $status_key;
                    }
                }

                $query->whereIn($filter, array_unique($result));
            } elseif (in_array($filter, self::FILTERS)) {
                $query->whereIn($filter, (array) $values[$key]);
            } elseif ($filter == 'search') {
                $search = array_map('mb_strtolower', (array) $values[$key]);
                //                $query->whereHasMorph(
                //                    'defectable',
                //                    OurTechnic::class,
                //                    function (Builder $q) use ($filter, $values, $key) {
                //                        $q->where(function ($qq) use ($filter, $key, $values) {
                //                            $filter_values = (array) $values[$key];
                //                            foreach ($filter_values as $value) {
                //                                $qq->orWhere($filter, 'like', '%' . $value . '%');
                //                            }
                //                        });
                //                    }
                //                );
                $query->where(function ($que) use ($search) {
                    $que->whereHasMorph(
                        'defectable',
                        OurTechnic::class,
                        function (Builder $q) use ($search) {
                            $q->where(function ($q) use ($search) {
                                foreach (self::TECHNIC_FILTERS as $filter) {
                                    foreach ($search as $value) {
                                        $q->orWhere($filter, 'like', '%'.$value.'%');
                                    }
                                }
                            });
                        }
                    );
                    $que->orWhereHasMorph(
                        'defectable',
                        FuelTank::class,
                        function (Builder $q) use ($search) {
                            $q->where(function ($q) use ($search) {
                                foreach (self::FUEL_TANK_FILTERS as $filter) {
                                    foreach ($search as $value) {
                                        $q->orWhere($filter, 'like', '%'.$value.'%');
                                    }
                                }
                            });
                        }
                    );
                });

            }
        }

        return $query;
    }

    /**
     * Return all defects if user have permission
     * and only related if not
     */
    public function scopePermissionCheck(Builder $query): Builder
    {
        $check = boolval(auth()->user()->hasPermission('tech_acc_defects_see'));

        if (! $check) {
            $query->where(function ($q) {
                $q->orWhere('user_id', auth()->id());
                $q->orWhere('responsible_user_id', auth()->id());
            });
        }

        return $query;
    }

    // Custom getters
    /**
     * Getter for nice status name
     */
    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status ?? 1];
    }

    /**
     * Getter for defectable owner name
     */
    public function getContractorAttribute(): ?string
    {
        return $this->defectable ? ($this->defectable->getMorphClass() == OurTechnic::class ? $this->defectable->owner : null) : null;
    }

    /**
     * Getter for repair_start_date formatting
     */
    public function getRepairStartAttribute(): ?Carbon
    {
        return $this->repair_start_date ? $this->repair_start_date->format(self::REPAIR_DATE_FORMAT) : null;
    }

    /**
     * Getter for repair_end_date formatting
     */
    public function getRepairEndAttribute(): ?Carbon
    {
        return $this->repair_end_date ? $this->repair_end_date->format(self::REPAIR_DATE_FORMAT) : null;
    }

    /**
     * Getter for created_at formatting
     */
    public function getCreatedAtFormattedAttribute(): ?Carbon
    {
        return $this->created_at->format(self::ADDITIONAL_DATE_FORMAT);
    }

    /**
     * Getter for author name
     */
    public function getAuthorNameAttribute(): ?string
    {
        return $this->author ? $this->author->full_name : null;
    }

    /**
     * Getter for responsible user name
     */
    public function getResponsibleUserNameAttribute(): ?string
    {
        return $this->responsible_user ? $this->responsible_user->full_name : null;
    }

    // Relations
    /**
     * Relation for defect author
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relation to defectable model
     */
    public function defectable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation for photos
     *
     * @return mixed
     */
    public function photos()
    {
        return $this->documents()->where('mime', 'like', '%image%');
    }

    /**
     * Relation for videos
     *
     * @return mixed
     */
    public function videos()
    {
        return $this->documents()->where('mime', 'like', '%video%');
    }

    /**
     * Relation for responsible user
     *
     * @return mixed
     */
    public function responsible_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id', 'id');
    }

    // Methods

    /**
     * @return mixed
     */
    public static function smartCreate(array $request)
    {
        $request['defectable_type'] = self::DEFECTABLE_TYPE[$request['defectable_type']];

        return self::create($request);
    }

    /**
     * Function generate route to defect card
     */
    public function card_route(): string
    {
        return route('building::tech_acc::defects.show', $this->id);
    }

    /**
     * Function return true if defect in diagnostics status
     */
    public function isInDiagnostics(): bool
    {
        return boolval($this->status == self::DIAGNOSTICS);
    }

    /**
     * Function return true if defect not in diagnostics status
     */
    public function isNotInDiagnostics(): bool
    {
        return ! $this->isInDiagnostics();
    }

    /**
     * Function return true if defect in new status
     */
    public function isNew(): bool
    {
        return boolval($this->status == self::NEW);
    }

    /**
     * Function return true if defect not in new status
     */
    public function isNotNew(): bool
    {
        return ! $this->isInDiagnostics();
    }

    /**
     * Function return true if defect in deleted status
     */
    public function isDeleted(): bool
    {
        return boolval($this->status == self::DELETED);
    }

    /**
     * Function return true if defect not in deleted status
     */
    public function isNotDeleted(): bool
    {
        return ! $this->isInDiagnostics();
    }

    /**
     * Function update active repair control tasks
     * if we have them
     *
     * @return mixed
     */
    public function updateActiveRepairControlTask(array $values)
    {
        return $this->active_tasks()->whereStatus(35)->update($values);
    }

    /**
     * This function solve all active defect tasks
     *
     * @return mixed
     */
    public function solveActiveTasks()
    {
        return $this->active_tasks->each(function ($task) {
            $task->solve_n_notify();
        });
    }
}
