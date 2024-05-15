<?php

namespace App\Models\Contractors;

use App\Models\Project;
use App\Models\ProjectContractors;
use App\Models\Task;
use App\Models\User;
use App\Notifications\Contractor\ContractorContactInformationRequiredNotice;
use App\Notifications\Contractor\UserCreatedContractorWithoutContactsNotice;
use App\Traits\DefaultSortable;
use App\Traits\DevExtremeDataSourceLoadable;
use App\Traits\SmartSearchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use DefaultSortable, DevExtremeDataSourceLoadable, SmartSearchable, SoftDeletes;

    protected $guarded = ['id'];

    public $defaultSortOrder = [
        'short_name' => 'asc',
    ];
    // protected $fillable = [
    //     'full_name', 'short_name', 'inn', 'kpp',
    //     'ogrn', 'legal_address', 'physical_adress',
    //     'general_manager', 'phone_number', 'email',
    //     'main_type', 'in_archive', 'notify', 'user_id'
    // ];

    protected $appends = ['types'];

    public static $notify_statuses = [
        '0' => 'Уведомлений о создании контактов не было',
        '1' => 'Было первичное уведомление (пользователю)',
        '2' => 'Было вторичное уведомление (начальнику)',
    ];

    // Создана табл contractor_types, пользоваться ей
    const CONTRACTOR_TYPES = [
        1 => 'Заказчик',
        2 => 'Подрядчик',
        // 3 => 'Поставщик',
        3 => 'Поставщик материалов',
        4 => 'Поставщик топлива',
    ];

    public $contractor_type = [
        1 => 'Генподряды',
        2 => 'Поставка',
        3 => 'Субподряд',
        4 => 'Услуги',
        5 => 'Оформление проектов',
        6 => 'Аренда техники',
        7 => 'Поставка топлива',
    ];

    // indexes from CONTRACTOR_TYPES
    const CUSTOMER = 1;

    const CONTRACTOR = 2;

    const SUPPLIER = 3;

    public function scopeByTypeSlug(Builder $query, $slug)
    {
        $mainTypeId = ContractorType::where('slug', $slug)->first()->id;
        $contractorAddotionalTypes = ContractorAdditionalTypes::where(
            'additional_type', $mainTypeId)->pluck('contractor_id'
            )->toArray();

        return
            $query
                ->where('main_type', $mainTypeId)
                ->orWhereIn('id', $contractorAddotionalTypes)
                ->get();
    }

    public function scopeByType(Builder $query, $type)
    {
        if ($type === 0) {
            return $query;
        }

        return $query->where(function ($q) use ($type) {
            $q->where('main_type', $type)
                ->orWhereHas('additional_types', function ($query) use ($type) {
                    $query->where('additional_type', $type);
                })
                ->orWhereNull('main_type');
        })->orderBy('main_type', 'desc');
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getTypesAttribute()
    {
        if ($this->additional_types->count()) {
            $types = $this->type_name;
            foreach ($this->additional_types as $type) {
                // $typeText = self::CONTRACTOR_TYPES[$type->additional_type];
                $typeText = ContractorType::find($type->additional_type)->name;
                $types .= ", {$typeText}";
            }

            return $types;
        } else {
            return $this->type_name;
        }
    }

    public function getTypeNameAttribute()
    {
        // return self::CONTRACTOR_TYPES[$this->main_type] ?? 'Не указан';
        return ContractorType::find($this->main_type)->name ?? 'Не указан';
    }

    public function file(): HasMany
    {
        return $this->hasMany(ContractorFile::class, 'contractor_id', 'id');
    }

    public function phones(): HasMany
    {
        return $this->hasMany(ContractorPhone::class, 'contractor_id', 'id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(ContractorContact::class, 'contractor_id', 'id');
    }

    public function creator(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function additions_projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_contractors')
            ->select('projects.*', 'contractors.short_name as contractor_name', 'contractors.inn as contractor_inn', 'contractors.id as contractor_id', 'users.last_name', 'users.first_name', 'users.patronymic', 'project_objects.name as project_name', 'project_objects.address as project_address', 'tasks.project_id', 'tasks.created_at as task_date')
            ->leftJoin('users', 'users.id', '=', 'projects.user_id')
            ->leftJoin('contractors', 'contractors.id', '=', 'projects.contractor_id')
            ->leftJoin('project_objects', 'project_objects.id', '=', 'projects.object_id')
            ->leftJoin('tasks', function ($query) {
                $query->on('projects.id', '=', 'tasks.project_id')
                    ->whereRaw('tasks.id IN (select MAX(a2.id) from tasks as a2 join projects as u2 on u2.id = a2.project_id group by u2.id)');
            })->with('author');
    }

    public function project_relations(): HasMany
    {
        return $this->hasMany(ProjectContractors::class, 'contractor_id', 'id');
    }

    /**
     * Relation for additional contractor types
     */
    public function additional_types(): HasMany
    {
        return $this->hasMany(ContractorAdditionalTypes::class, 'contractor_id', 'id');
    }

    public function hasRemoveRequest()
    {
        // find remove task
        return Task::where('status', 19)->where('target_id', $this->id)->where('is_solved', 0)->first();
    }

    public function create_notify($diff_in_days)
    {
        if ($this->notify == 0 and $diff_in_days == 1) {
            // send first notification to creator
            ContractorContactInformationRequiredNotice::send(
                $this->user_id,
                [
                    'name' => 'Заполните контакты контрагента '.$this->short_name,
                    'additional_info' => 'Ссылка на контрагента: ',
                    'url' => route('contractors::card', $this->id),
                    'contractor_id' => $this->id,
                    'status' => 5,
                ]
            );

            $this->notify = 1;
        } elseif ($this->notify >= 1 and $diff_in_days >= 2) {
            // send second notification (maybe not for first time)
            // check contractor creator -> load creator relation
            $this->load('creator');

            if ($this->creator->isInGroup(50)/*7*/) {
                // creator is head of sell department -> send notify to General Director
                $chief_id = User::where('group_id', 5/*3*/)->first()->id;
            } else {
                // creator is standard user from sell department -> send notification to head of sell department
                $chief_id = User::where('group_id', 50/*7*/)->first()->id;
            }

            UserCreatedContractorWithoutContactsNotice::send(
                $chief_id,
                [
                    'name' => 'Пользователь '.$this->creator->full_name.' не заполнил(а) контактов контрагента '.$this->short_name,
                    'additional_info' => 'Ссылка на контрагента: ',
                    'url' => route('contractors::card', $this->id),
                    'user_id' => $chief_id,
                    'contractor_id' => $this->id,
                    'status' => 5,
                ]
            );

            $this->notify = 2;
        }

        $this->save();

        return true;
    }
}
