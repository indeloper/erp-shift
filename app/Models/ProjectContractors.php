<?php

namespace App\Models;

use App\Models\Contractors\Contractor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ProjectContractors extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'contractor_id',
        'user_id',
    ];


    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }


    public function contractor()
    {
        return $this->belongsTo(Contractor::class, 'contractor_id', 'id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function useAsMain()
    {
        // find project, old_contractor and new_contractor
        $project = Project::findOrFail($this->project_id);
        $old_contractor = $project->contractor_id;
        $new_contractor = $this->contractor_id;
        // update
        $updated_contractor = $project->update(['contractor_id' => $new_contractor]);
        $updated_relation = $this->update(['contractor_id' => $old_contractor]);
        // create history
        ProjectContractorsChangeHistory::create([
            'project_id' => $project->id,
            'old_contractor_id' => $old_contractor,
            'new_contractor_id' => $new_contractor,
            'user_id' => Auth::id()
        ]);

        return true;
    }

    public function getCreatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return \Carbon\Carbon::parse($date)->format('d.m.Y H:i:s');
    }
}
