<?php

namespace App\Models\Contract;

use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Contractors\Contractor;
use App\Models\MatAcc\MaterialAccountingOperation;
use App\Models\Project;
use App\Models\ProjectResponsibleUser;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Contract extends Model
{
    use HasFactory;

    public $contract_status = [
        1 => 'В работе',
        2 => 'На согласовании',
        3 => 'Отклонен',
        4 => 'Согласовано',
        5 => 'На гарантии',
        6 => 'Подписан',
    ];

    public $contract_types = [
        1 => 'Договор с заказчиком',
        2 => 'Договор поставки',
        3 => 'Договор субподряда',
        4 => 'Договор услуг',
        5 => 'Договор на оформление проекта',
        6 => 'Договор на аренду техники',
        7 => 'Доп. соглашение',
    ];

    protected $fillable = ['name', 'user_id', 'commercial_offer_id', 'subcontractor_id', 'garant_file_name',
        'final_file_name', 'project_id', 'version', 'status', 'foreign_id', 'contract_id', 'main_contract_id', 'ks_date', 'start_notifying_before', 'type'];

    protected $appends = ['name_for_humans', 'internal_id', 'status_text', 'contractor_name'];

    public function getStatusTextAttribute()
    {
        return $this->contract_status[$this->status];
    }

    /**
     * Scope for contracts that have 'КС' date in the interval of ten days,
     * only for first type
     *
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeTenDaysBeforeDateOfKC(Builder $query)
    {
        // Take first type only and not empty ks_date
        $query->whereType(1)->whereNotNull('ks_date');

        // Only ten days interval
        $query->where('ks_date', 'like', '%'.now()->addDays(10)->format('d'));

        return $query;
    }

    /**
     * Morph relation to tasks
     *
     * @return MorphMany
     */
    public function tasksMorphed()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Base scope
     */
    public function scopeBase(Builder $query): Builder
    {
        return $query->orderBy('id', 'desc')
            ->leftJoin('users', 'users.id', '=', 'contracts.user_id')
            ->leftJoin('projects', 'projects.id', '=', 'contracts.project_id')
            ->leftJoin('project_objects', 'project_objects.id', 'projects.object_id')
            ->leftJoin('contractors', 'contractors.id', '=', 'projects.contractor_id')
            ->select('contracts.*', 'users.last_name', 'users.first_name', 'users.patronymic',
                'projects.contractor_id', 'projects.name as project_name',
                'project_objects.address as object_address', 'projects.entity as project_entity', 'contractors.id as contractor_id');
    }

    /**
     * Return contracts for given filter.
     */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        $search = $request->filters ?? [];
        $values = $request->values ?? [];

        foreach ($search as $key => $iter) {
            if ($iter === 'project_objects_address') {
                $iter = 'project_objects.address';
            } else {
                $iter = preg_replace('/_/', '.', $iter, 1);
            }
            if ($iter === 'contracts.created_at') {
                $dates = explode('|', $values[$key]);
                $from = Carbon::createFromFormat('d.m.Y', $dates[0])->toDateString();
                $to = Carbon::createFromFormat('d.m.Y', $dates[1])->toDateString();
                $query->whereDate($iter, '>=', $from)->whereDate($iter, '<=', $to);
            } elseif (in_array($iter, ['contracts.foreign_id', 'contractors.short_name', 'contracts.status', 'projects.entity'])) {
                foreach ((array) $values[$key] as $value) {
                    $query->where(function ($sub_q) use ($iter, $value) {
                        $sub_q->where($iter, 'like', "%$value%");
                        if ($iter == 'contractors.short_name') {
                            $sub_q->orWhereHas('subcontractor', function ($subc_query) use ($iter, $value) {
                                $subc_query->where($iter, 'like', "%{$value}%");
                            });
                        }
                    });
                }
            } elseif ($iter === 'search' && $searches = explode('•', $values[$key])) {
                foreach ($searches as $search) {
                    $query->where(function ($firstQuery) use ($search) {
                        $firstQuery->where('contracts.contract_id', 'like', "%{$search}%")
                            ->orWhere('contracts.foreign_id', 'like', "%{$search}%")
                            ->orWhere('contractors.short_name', 'like', "%{$search}%")
                            ->orWhere('project_objects.address', 'like', "%{$search}%")
                            ->orWhere('projects.name', 'like', "%{$search}%")
                            ->orWhere('contracts.name', 'like', "%{$search}%")
                            ->orWhereHas('subcontractor', function ($subc_query) use ($search) {
                                $subc_query->where('short_name', 'like', "%{$search}%");
                            })
                            ->orWhere(function ($q) use ($search) {
                                $string = mb_strtolower($search);
                                $result = array_filter(Contract::getModel()->contract_status, function ($item) use ($string) {
                                    return stristr(mb_strtolower($item), $string);
                                });

                                if (count($result)) {
                                    $q->whereIn('contracts.status', array_keys($result));
                                }
                            })->orWhere(function ($q) use ($search) {
                                $string = mb_strtolower($search);
                                $result = array_filter(Project::$entities, function ($item) use ($string) {
                                    return stristr(mb_strtolower($item), $string);
                                });
                                if (count($result)) {
                                    $q->whereIn('projects.entity', array_keys($result));
                                }
                            });
                    });
                }
            } else {
                $query->whereIn($iter, (array) $values[$key]);
            }
        }

        return $query;
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'target_id', 'id')->whereIn('status', Task::CONTR_STATUS);
    }

    public function subcontractor()
    {
        return $this->hasOne(Contractor::class, 'id', 'subcontractor_id');
    }

    public function getContractorNameAttribute()
    {
        if ($this->subcontractor()->exists()) {
            return $this->subcontractor()->first()->short_name;
        } else {
            return $this->project->contractor->short_name;
        }
    }

    public function commercial_offers()
    {
        return $this->belongsToMany(CommercialOffer::class, 'contract_commercial_offer_relations', 'contract_id', 'commercial_offer_id');
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getKsDateAndNotifyDateTextAttribute()
    {
        if (! $this->ks_date) {
            return '-';
        }
        $date = Carbon::now();
        if ($date->day > $this->ks_date) {
            $date->month++;
            $date->day = $this->ks_date;
        }

        $notify_date = $date->copy();
        $notify_date->day -= ($this->start_notifying_before ?? 10);

        return "{$date->isoFormat('DD.MM')} ({$notify_date->isoFormat('D.MM')})";
    }

    public function unsolved_tasks()
    {
        return $this->hasMany(Task::class, 'target_id', 'id')->where(function ($q) {
            $q->orWhere('is_solved', 0)->orWhere('revive_at', '<>', null);
        })->whereIn('status', Task::CONTR_STATUS)->get();
    }

    public function operations()
    {
        return $this->hasMany(MaterialAccountingOperation::class, 'contract_id', 'id');
    }

    public function get_prev_task()
    {
        if ($this->status == 1) {
            $prev_contract = Contract::whereContractId($this->contract_id)->whereVersion($this->version - 1)->get()->last();
            if ($prev_contract) {
                $prev_task = $prev_contract->tasks()->latest()->take(2); //TODO: replace this with number of responsible users
            } else {
                return new Task();
            }
        } elseif ($this->status == 2) {
            $prev_task = $this->tasks()->where('status', 7)->latest()->take(2);
        } elseif ($this->status == 4) {
            $prev_task = $this->tasks()->where('status', 11)->latest()->take(2);
        } elseif ($this->status == 5) {
            $prev_task = $this->tasks()->where('status', 9)->latest()->take(2);
        } elseif ($this->status == 6) {
            $prev_task = $this->tasks()->where('status', 10)->latest()->take(2);
        } elseif ($this->status == 3) {
            $prev_task = $this->tasks()->latest()->take(2);
        } else {
            return new Task();
        }

        $user_prev = $prev_task->where('responsible_user_id', Auth::id());
        if ($user_prev->count()) {
            $prev_task = $user_prev;
        }

        return $prev_task ? $prev_task->get()->first() : new Task();
    }

    public function get_requests()
    {
        return $this->hasMany(ContractRequest::class, 'contract_id', 'id');
    }

    public function theses_check()
    {
        return $this->hasMany(ContractThesis::class, 'contract_id', 'id')
            ->leftJoin('contract_thesis_verifiers', 'contract_thesis_verifiers.thesis_id', 'contract_theses.id')
            ->where('contract_thesis_verifiers.user_id', Auth::user()->id)
            ->select('contract_theses.*', 'contract_thesis_verifiers.user_id as verifier_id', 'contract_thesis_verifiers.thesis_id', 'contract_thesis_verifiers.status as verifier_status');
    }

    public function theses()
    {
        return $this->hasMany(ContractThesis::class, 'contract_id', 'id');
    }

    public function works()
    {
        return $this->hasMany(CommercialOffer::class, 'id', 'commercial_offer_id')
            ->leftJoin('work_volumes', 'work_volumes.id', 'commercial_offers.work_volume_id')
            ->leftJoin('work_volume_works', 'work_volume_works.work_volume_id', 'work_volumes.id')
            ->leftJoin('manual_works', 'work_volume_works.manual_work_id', 'manual_works.id')
            ->select('commercial_offers.id', 'work_volume_works.result_price', 'work_volume_works.subcontractor_id', 'work_volume_works.count', 'work_volume_works.term', 'manual_works.name', 'manual_works.unit', 'manual_works.price_per_unit');
    }

    public function files()
    {
        return $this->hasMany(ContractFiles::class, 'contract_id', 'id');
    }

    public function extra_contracts()
    {
        return $this->hasMany(Contract::class, 'main_contract_id');
    }

    public function main_contract()
    {
        return $this->hasOne(Contract::class, 'id', 'main_contract_id');
    }

    public function GetNameForHumansAttribute()
    {
        if ($this->type == 7) {
            return $this->name.' к договору № '.$this->internal_id.' '.($this->foreign_id ? ' ('.$this->foreign_id.')-' : '').'V'.$this->version;
        } else {
            return $this->name.' № '.$this->internal_id.' '.($this->foreign_id ? ' ('.$this->foreign_id.')-' : '').'V'.$this->version;
        }
    }

    public function GetInternalIdAttribute()
    {
        if ($this->type == 7) {
            return $this->main_contract->contract_id;
        } else {
            return $this->contract_id;
        }
    }

    public function responsible_user_ids()
    {
        return $this->hasMany(ProjectResponsibleUser::class, 'project_id', 'project_id')->where('role', 7)->select('user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function hasRemoveRequest()
    {
        // find remove task
        return Task::where('status', 20)->where('target_id', $this->id)->where('is_solved', 0)->first();
    }

    public function manual_delete()
    {
        DB::beginTransaction();

        // remove relations and contract, solve tasks
        $contract_files = ContractFiles::where('contract_id', $this->id)->delete();
        $contract_request = ContractRequest::where('contract_id', $this->id);
        $contract_request_files = ContractRequestFile::whereIn('request_id', $contract_request->pluck('id')->toArray())->delete();
        $contract_request->delete();
        $contract_theses = ContractThesis::where('contract_id', $this->id);
        $contract_theses_verifiers = ContractThesisVerifier::whereIn('thesis_id', $contract_theses->pluck('id')->toArray())->delete();
        $contract_theses->delete();
        $this->load('tasks');

        foreach ($this->tasks as $task) {
            $task->solve_n_notify();
        }

        $this->delete();

        DB::commit();
    }

    public function key_dates()
    {
        return $this->hasMany(ContractKeyDates::class)->whereNull('key_date_id');
    }

    public function key_date_values(): Collection
    {
        return ContractKeyDatesPreselectedNames::all();
    }

    public function decline()
    {
        if ($this->whereNotIn('status', [5, 6])) {
            $this->tasks->each(function ($task) {
                $task->solve_n_notify();
            });

            $this->theses()->where('status', 1)->update(['status' => 2]);

            $this->status = 3;

            $this->push();

            return true;
        }

        return false;
    }
}
