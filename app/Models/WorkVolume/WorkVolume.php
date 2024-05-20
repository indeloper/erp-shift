<?php

namespace App\Models\WorkVolume;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class WorkVolume extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['user_id', 'project_id', 'version', 'status', 'is_save_tongue', 'is_save_pile', 'depth', 'type', 'option'];

    public $wv_status = [
        1 => 'В работе',
        2 => 'Отправлен',
        3 => 'Отклонен',
    ];

    public $wv_type = [
        0 => 'Шпунтовое направление',
        1 => 'Свайное направление',
    ];

    protected $appends = ['type_name', 'status_name'];

    public function getTypeNameAttribute()
    {
        return $this->wv_type[$this->type];
    }

    public function getStatusNameAttribute()
    {
        return $this->wv_status[$this->status];
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'target_id', 'id')->whereIn('status', Task::WV_STATUS);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function made_task(): HasOne
    {
        return $this->hasOne(Task::class, 'target_id', 'id')->whereIn('status', [3, 4]);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(WorkVolumeRequest::class, 'work_volume_id', 'id');
    }

    public function raw_works(): HasMany
    {
        return $this->hasMany(WorkVolumeWork::class, 'work_volume_id', 'id');
    }

    public function works(): HasMany
    {
        return $this->hasMany(WorkVolumeWork::class, 'work_volume_id', 'id')
            ->leftJoin('manual_works', 'manual_works.id', '=', 'work_volume_works.manual_work_id')
            ->select('work_volume_works.*', 'manual_works.work_group_id')
            ->orderBy('order');
    }

    public function worksWithoutManualInfo(): HasMany
    {
        return $this->hasMany(WorkVolumeWork::class, 'work_volume_id', 'id');
    }

    public function works_offer(): HasMany
    {
        return $this->hasMany(WorkVolumeWork::class, 'work_volume_id', 'id')
//            ->where('work_volume_works.is_tongue', $this->type ? 0 : 1)
            ->leftJoin('contractor_files', 'contractor_files.id', '=', 'work_volume_works.subcontractor_file_id')
            ->leftJoin('contractors', 'contractors.id', '=', 'contractor_files.contractor_id')
            ->leftJoin('manual_works', 'manual_works.id', '=', 'work_volume_works.manual_work_id')
            ->select('work_volume_works.*', 'contractors.short_name as contractor_name', 'manual_works.work_group_id')
            ->orderBy('order');
    }

    public function works_offer_double(): HasMany
    {
        return $this->hasMany(WorkVolumeWork::class, 'work_volume_id', 'id')
            //->where('work_volume_works.is_tongue', $this->type ? 0 : 1)
            ->leftJoin('contractor_files', 'contractor_files.id', '=', 'work_volume_works.subcontractor_file_id')
            ->leftJoin('contractors', 'contractors.id', '=', 'contractor_files.contractor_id')
            ->leftJoin('manual_works', 'manual_works.id', '=', 'work_volume_works.manual_work_id')
            ->select('work_volume_works.*', 'contractors.short_name as contractor_name', 'manual_works.work_group_id')
            ->orderBy('order');
    }

    public function works_tongue(): HasMany
    {
        return $this->hasMany(WorkVolumeWork::class, 'work_volume_id', 'id')->where('is_tongue', 1)
            ->orderBy('order');
    }

    public function works_pile(): HasMany
    {
        return $this->hasMany(WorkVolumeWork::class, 'work_volume_id', 'id')->where('is_tongue', 0)
            ->orderBy('order');
    }

    public function get_requests(): HasMany
    {
        return $this->hasMany(WorkVolumeRequest::class, 'work_volume_id', 'id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(WorkVolumeMaterial::class, 'work_volume_id', 'id');
    }

    public function shown_materials()
    {
        return $this->materials()->where('complect_id', null);
    }

    public function complect_materials()
    {
        return $this->shown_materials()->where('material_type', 'complect');
    }

    public function regular_materials()
    {
        return $this->shown_materials()->where('material_type', 'regular');
    }

    public function save_request(string $req_description, string $req_name, $documents, $project_documents)
    {
        $wv_request = new WorkVolumeRequest();
        $wv_request->name = $req_name;
        $wv_request->user_id = Auth::id();
        $wv_request->project_id = $this->project_id;
        $wv_request->work_volume_id = $this->id;
        $wv_request->status = 0;
        $wv_request->tongue_pile = $this->type;
        $wv_request->description = $req_description;
        $wv_request->save();

        $wv_request->save_documents($documents);
        $wv_request->addach_project_documents($project_documents);
    }

    public function decline()
    {
        $this->tasks->each(function ($task) {
            $task->solve_n_notify();
        });

        $this->requests()->where('status', 0)->update(['status' => 2]);

        $this->status = 3;

        $this->push();

        return true;
    }
}
