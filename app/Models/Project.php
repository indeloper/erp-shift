<?php

namespace App\Models;

use App\Events\ProjectEvents;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Contract\Contract;
use App\Models\Contractors\Contractor;
use App\Models\WorkVolume\WorkVolume;
use App\Traits\Logable;
use App\Traits\Taskable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{

    use HasFactory;
    use Logable, SoftDeletes, Taskable;

    protected $guarded = [];

    public $project_status
        = [
            1 => 'Запрос от клиента',
            2 => 'Расчёт объёмов',
            3 => 'Формирование КП',
            8 => 'Согласование КП с заказчиком',
            4 => 'Формирование договора',
            7 => 'Договоры подписаны',
            //5 => 'Завершен',
            5 => 'Не реализован',
            6 => 'Закрыт',
        ];

    public $project_status_description
        = [
            1 => 'Выявление потребностей заказчика и составление заявки на расчет объемов работ',
            2 => 'Формирование объемов работ инженером ПТО на основании заявки',
            3 => 'Оценка стоимости проекта, формирование и согласование коммерческого предложения с заказчиком',
            8 => 'Согласование КП с заказчиком',
            4 => 'Формирование договоров на основании согласованного коммерческого предложения',
            7 => 'Договоры подписаны. Проект готов к производству работ',
            //5 => 'Завершен',
            5 => 'Не было достигнуто соглашения по коммерческому предложению или договорам',
            6 => 'Проект перемещен в архив',
        ];

    public static $entities
        = [
            1 => 'ООО «СК ГОРОД»',
            2 => 'ООО «ГОРОД»',
        ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($project) {
            event((new ProjectEvents())->projectCreated($project));
        });

        //        static::updated(function($item) { //we might need it in future
        //            Event::fire('item.updated', $item);
        //        });
    }

    /**
     * Return projects that have contracts in status 5 or 6,
     * what equals to Contracts work start
     */
    public function scopeContractsStarted(Builder $query): Builder
    {
        return $query->has('ready_contracts');
    }

    public function getTongueStatusesAttribute()
    {
        $wvs = $this->wvs
            ->where('type', 0)
            ->where('status', 1)
            // ->groupBy('option')
            ->pluck('option')
            ->unique();

        $com_offers_send = $this->com_offers
            ->where('is_tongue', 1)
            ->where('status', 5)
            // ->groupBy('option')
            ->whereNotIn('option', $wvs)
            ->pluck('option')
            ->unique();

        $com_offers = $this->com_offers
            ->where('is_tongue', 1)
            ->whereNotIn('status', [3, 4, 5])
            // ->groupBy('option')
            ->whereNotIn('option', $wvs)
            ->whereNotIn('option', $com_offers_send)
            ->pluck('option', 'status')
            ->unique();

        $com_offers_complete = $this->com_offers
            ->where('is_tongue', 1)
            ->where('status', 4)
            // ->groupBy('option')
            ->whereNotIn('option', $wvs)
            ->pluck('option', 'status')
            ->unique();

        $wvs_complete = $this->wvs
            ->where('type', 0)
            ->where('status', 2)
            ->whereNotIn('option', $com_offers)
            ->whereNotIn('option', $wvs)
            ->whereNotIn('option', $com_offers_send)
            ->whereNotIn('option', $com_offers_complete)
            ->pluck('option')
            ->unique();

        $contracts = $this->contracts->where('status', '!=', 6);

        $contracts          = $contracts->filter(function ($item, $key) use (
            $com_offers
        ) {
            return $item->commercial_offers->count()
                && $item->commercial_offers->whereNotIn('option', $com_offers)
                    ->where('is_tongue', 1)->count();
        });
        $contracts_complete = $this->contracts->whereNotIn('id',
            $contracts->pluck('id'));

        $contracts_complete = $contracts_complete->filter(function (
            $item,
            $key
        ) use ($com_offers) {
            return $item->commercial_offers->count()
                && $item->commercial_offers->whereNotIn('option', $com_offers)
                    ->where('is_tongue', 1)->count();
        })->unique('commercial_offer_id');

        $statuses = '';
        foreach ($wvs as $option) {
            $statuses .= ($option ? '<b>'.$option : '<b>Стандарт')
                .'</b>: Расчёт объемов'.'</br>';
        }

        foreach ($wvs_complete as $option) {
            $statuses .= ($option ? '<b>'.$option : '<b>Стандарт')
                .'</b>: Формирование КП'.'<br>';
        }

        foreach ($com_offers as $option) {
            $statuses .= ($option ? '<b>'.$option : '<b>Стандарт')
                .'</b>: Формирование КП'.'<br>';
        }

        foreach ($com_offers_send as $option) {
            $statuses .= ($option ? '<b>'.$option : '<b>Стандарт')
                .'</b>: Согласование КП с заказчиком'.'<br>';
        }

        foreach ($contracts ?? $com_offers_complete as $contract) {
            $statuses .= ($contract->commercial_offers->first()->option != ''
                    ? '<b>'.$contract->commercial_offers->first()->option
                    : '<b>Стандарт').'</b>: Формирование договоров'.'<br>';
        }

        if ( ! $contracts->count() && ! $contracts_complete->count()) {
            foreach ($com_offers_complete as $option) {
                $statuses .= ($option ? '<b>'.$option : '<b>Стандарт')
                    .'</b>: Формирование договоров'.'<br>';
            }
        }

        foreach (
            $this->wvs()->where('type', 0)->groupBy('option')->get() as $item
        ) {
            if ($this->in_not_realized($item->option, 1)) {
                $statuses .= ($item->option != '' ? '<b>'.$item->option
                        : '<b>Стандарт').'</b>: Не реализовн'.'<br>';
            }
        }

        foreach ($contracts_complete as $contract) {
            $statuses .= ($contract->commercial_offers->first()->option != ''
                    ? '<b>'.$contract->commercial_offers->first()->option
                    : '<b>Стандарт').'</b>: Договор подписан'.'<br>';
        }

        if ($this->is_tongue && $statuses == ''
            && ! $this->wvs->where('type', 0)->count()
        ) {
            $statuses .= 'Запрос от клиента';
        }

        return $statuses;
    }

    public function getPileStatusesAttribute()
    {
        $wvs = $this->wvs
            ->where('type', 1)
            ->where('status', 1)
            // ->groupBy('option')
            ->pluck('option')
            ->unique();

        $com_offers_send = $this->com_offers
            ->where('is_tongue', 0)
            ->whereIn('status', [5])
            // ->groupBy('option')
            ->whereNotIn('option', $wvs)
            ->pluck('option')
            ->unique();

        $com_offers = $this->com_offers
            ->where('is_tongue', 0)
            ->whereNotIn('status', [3, 4, 5])
            // ->groupBy('option')
            ->whereNotIn('option', $wvs)
            ->whereNotIn('option', $com_offers_send)
            ->pluck('option')
            ->unique();

        $com_offers_complete = $this->com_offers
            ->where('is_tongue', 0)
            ->where('status', 4)
            // ->groupBy('option')
            ->whereNotIn('option', $wvs)
            ->whereNotIn('option', $com_offers_send)
            ->pluck('option')
            ->unique();

        $wvs_complete = $this->wvs
            ->where('type', 1)
            ->where('status', 2)
            ->whereNotIn('option', $com_offers)
            ->whereNotIn('option', $wvs)
            ->whereNotIn('option', $com_offers_send)
            ->whereNotIn('option', $com_offers_complete)
            ->pluck('option')
            ->unique();

        $contracts = $this->contracts->where('status', '!=', 6);

        $contracts = $contracts->filter(function ($item, $key) use ($com_offers
        ) {
            return $item->commercial_offers->count()
                && $item->commercial_offers->whereNotIn('option', $com_offers)
                    ->where('is_tongue', 0)->count();
        });

        $contracts_complete = $this->contracts->whereNotIn('id',
            $contracts->pluck('id'));

        $contracts_complete = $contracts_complete->filter(function (
            $item,
            $key
        ) use ($com_offers) {
            return $item->commercial_offers->count()
                && $item->commercial_offers->whereNotIn('option', $com_offers)
                    ->where('is_tongue', 0)->count();
        })->unique('commercial_offer_id');

        // dump($com_offers_complete, $contracts);

        $statuses = '';
        foreach ($wvs as $option) {
            $statuses .= ($option ? '<b>'.$option : '<b>Стандарт')
                .'</b>: Расчёт объемов'.'<br>';
        }

        foreach ($wvs_complete as $option) {
            $statuses .= ($option ? '<b>'.$option : '<b>Стандарт')
                .'</b>: Формирование КП'.'<br>';
        }

        foreach ($com_offers as $option) {
            $statuses .= ($option ? '<b>'.$option : '<b>Стандарт')
                .'</b>: Формирование КП'.'<br>';
        }

        foreach ($com_offers_send as $option) {
            $statuses .= ($option ? '<b>'.$option : '<b>Стандарт')
                .'</b>: Согласование КП с заказчиком'.'<br>';
        }

        // dump($contracts, $com_offers_complete);
        foreach ($contracts as $contract) {
            $statuses .= ($contract->commercial_offers->first()->option != ''
                    ? '<b>'.$contract->commercial_offers->first()->option
                    : '<b>Стандарт').'</b>: Формирование договоров'.'<br>';
        }

        if ( ! $contracts->count() && ! $contracts_complete->count()) {
            foreach ($com_offers_complete as $option) {
                $statuses .= ($option ? '<b>'.$option.'</br>' : '<b>Стандарт')
                    .'</b>: Формирование договоров'.'<br>';
            }
        }

        foreach (
            $this->wvs()->where('type', 1)->groupBy('option')->get() as $item
        ) {
            if ($this->in_not_realized($item->option, 0)) {
                $statuses .= ($item->option != '' ? '<b>'.$item->option
                        : '<b>Стандарт').'</b>: Не реализовн'.'<br>';
            }
        }

        foreach ($contracts_complete as $contract) {
            $statuses .= ($contract->commercial_offers->first()->option != ''
                    ? '<b>'.$contract->commercial_offers->first()->option
                    : '<b>Стандарт').'</b>: Договор подписан'.'<br>';
        }

        if ($this->is_pile && $statuses == ''
            && ! $this->wvs->where('type', 1)->count()
        ) {
            $statuses .= 'Запрос от клиента';
        }

        return $statuses;
    }

    public function getRpNamesAttribute()
    {
        $RPs      = $this->respUsers()->whereIn('role', [5, 6])->get();
        $users    = User::find($RPs->pluck('user_id'));
        $rp_names = '';
        foreach ($users as $user) {
            $rp_names .= $user->full_name.'; ';
        }

        return $rp_names ?: 'Пока не назначен';
    }

    public function in_not_realized($option, $is_tongue)
    {
        $type                    = $is_tongue == 1 ? 0 : 1;
        $all_wvs_declined        = $this->wvs->where('type', $type)
                ->where('option', $option)->count() == $this->wvs->where('type',
                $type)->where('option', $option)->where('status', 3)->count();
        $all_com_offers_declined = ($this->com_offers->where('is_tongue',
                    $is_tongue)->where('option', $option)->count()
                == $this->com_offers->where('is_tongue', $is_tongue)
                    ->where('option', $option)->where('status', 3)->count())
            && $this->com_offers->where('is_tongue', $is_tongue)
                ->where('option', $option)->count() != 0;

        if ($all_wvs_declined && $all_com_offers_declined) {
            return true;
        }

        return false;
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getContractorNameAttribute()
    {
        return $this->contractor->short_name;
    }

    /**
     * Function generate project name with object name tag
     */
    public function getNameWithObjectAttribute(): string
    {
        $objectName = $this->object ? $this->object->name_tag : '';

        return "{$this->name} - {$objectName}";
    }

    public static function getAllProjects(): Builder
    {
        return Project::select('projects.*',
            'contractors.short_name as contractor_name',
            'contractors.inn as contractor_inn',
            'contractors.id as contractor_id', 'users.last_name',
            'users.first_name', 'users.patronymic',
            'project_objects.name as project_name',
            'project_objects.address as project_address',
            'project_objects.short_name as object_short_name',
            'tasks.project_id', 'tasks.created_at as task_date')
            ->leftJoin('users', 'users.id', '=', 'projects.user_id')
            ->leftJoin('contractors', 'contractors.id', '=',
                'projects.contractor_id')
            ->leftJoin('project_objects', 'project_objects.id', '=',
                'projects.object_id')
            ->leftJoin('tasks', function ($query) {
                $query->on('projects.id', '=', 'tasks.project_id')
                    ->whereRaw('tasks.id IN (select MAX(a2.id) from tasks as a2 join projects as u2 on u2.id = a2.project_id group by u2.id)');
            })->with('author')
            ->orderByRaw('CASE WHEN projects.is_important = 1 THEN 1 ELSE 2 END, task_date DESC');
    }

    /**
     * Function make project important if it was not important
     * and not important if it was important
     */
    public function importanceToggler()
    {
        $this->is_important == 0 ? $this->toggleImportance()
            : $this->disableImportance();

        return $this->save();
    }

    /**
     * Function make given project important
     */
    public function toggleImportance()
    {
        $this->is_important = 1;
    }

    /**
     * Function make given project not important
     */
    public function disableImportance()
    {
        $this->is_important = 0;
    }

    /**
     * Relation for time responsible user
     */
    public function timeResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'time_responsible_user_id', 'id');
    }

    public function work_volumes(): HasMany
    {
        return $this->hasMany(WorkVolume::class, 'project_id', 'id');
    }

    public function author(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function wvs(): HasMany
    {
        return $this->hasMany(WorkVolume::class, 'project_id', 'id');
    }

    public function com_offers(): HasMany
    {
        return $this->hasMany(CommercialOffer::class, 'project_id', 'id');
    }

    public function respUsers(): HasMany
    {
        return $this->hasMany(ProjectResponsibleUser::class, 'project_id',
            'id');
    }

    public function object(): HasOne
    {
        return $this->hasOne(ProjectObject::class, 'id', 'object_id');
    }

    public function last_task(): HasOne
    {
        return $this->hasOne(Task::class, 'project_id', 'id')
            ->with('responsible_user', 'author', 'redirects', 'task_files')
            ->leftJoin('users', 'users.id', '=', 'tasks.responsible_user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->leftjoin('work_volumes', 'tasks.target_id', 'work_volumes.id')
            ->leftjoin('project_objects', 'project_objects.id',
                'projects.object_id')
            ->select('users.last_name', 'users.first_name', 'users.patronymic',
                'projects.name as project_name',
                'contractors.short_name as contractor_name',
                'work_volumes.type', 'work_volumes.id as work_volume_id',
                'project_objects.address as object_address', 'tasks.*')
            ->orderBy('created_at', 'desc');
    }

    public function all_tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'project_id', 'id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'project_id');
    }

    public function ready_contracts()
    {
        return $this->contracts()->whereIn('status', [5, 6]);
    }

    public function contractors(): HasMany
    {
        return $this->hasMany(ProjectContractors::class, 'project_id', 'id');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
    }

    public function scopeMaterialFilter(Builder $q, $material_names)
    {
        foreach ($material_names as $name) {
            $q->orWhereHas('work_volumes.materials',
                function ($query) use ($name) {
                    $query->whereHasMorph('manual',
                        [\App\Models\Manual\ManualMaterial::class],
                        function ($mat) use ($name) {
                            $mat->where('name', 'like', '%'.$name.'%');
                            $mat->where('material_type', 'regular');
                        });
                });
        }
    }

}
