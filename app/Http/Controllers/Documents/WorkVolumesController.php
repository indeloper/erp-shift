<?php

namespace App\Http\Controllers\Documents;

use App\Models\Department;
use App\Models\Group;
use App\Models\Manual\ManualMaterial;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WorkVolumesController extends Controller
{
    public function index(Request $request)
    {
        $work_volumes = WorkVolume::has('made_task')->with('made_task')
            ->orderBy('id', 'desc')
            ->leftjoin('users', 'users.id', '=', 'work_volumes.user_id')
            ->leftjoin('projects', 'projects.id', '=', 'work_volumes.project_id')
            ->leftjoin('contractors', 'contractors.id', '=', 'projects.contractor_id')
            ->leftjoin('project_objects', 'project_objects.id', '=', 'projects.object_id')
            ->where('type', '!=', 2)
//            ->whereIn('work_volumes.id', [DB::raw('select max(work_volumes.id) from work_volumes GROUP BY project_id, type')])
            ->select('work_volumes.*', DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) as user_full_name'),
                'projects.name as project_name', 'projects.entity as project_entity', 'contractors.short_name as contractor_name',
                'contractors.id as contractor_id', 'project_objects.address as address');

        $material_names = [];
        $mat_names = [];

        if ($request->has(['search', 'values', 'parameters']) and $request->search) {
            $search = explode(',', $request->search);
            $values = explode(',', $request->values);

            $old_request = $request->all();

            foreach ($search as $key => $iter) {
                if (in_array($iter, ['work_volumes.updated_at', 'work_volumes.status', 'work_volumes.type', 'user', 'projects.entity'])) {
                    if ($iter == 'work_volumes.updated_at') {
                        $dates = explode('|', $values[$key]);
                        $from = Carbon::createFromFormat('d.m.Y', $dates[0])->toDateString();
                        $to = Carbon::createFromFormat('d.m.Y', $dates[1])->toDateString();
                        $work_volumes->whereDate($iter, '>=', $from)->whereDate($iter, '<=', $to);
                    } else if ($iter == 'work_volumes.status') {
                        $search = mb_strtolower($values[$key]);
                        $result = array_filter($work_volumes->getModel()->wv_status, function($item) use ($search) {
                            return stristr(mb_strtolower($item), $search);
                        });

                        $work_volumes->WhereIn($iter, array_keys($result));
                    } else if ($iter == 'work_volumes.type') {
                        $search = mb_strtolower($values[$key]);
                        $result = array_filter($work_volumes->getModel()->wv_type, function($item) use ($search) {
                            return stristr(mb_strtolower($item), $search);
                        });

                        $work_volumes->whereIn($iter, array_keys($result));
                    } else if ($iter == 'user') {
                        $users = User::getAllUsers();
                        $search = mb_strtolower($values[$key]);
                        $groups = Group::where('name', $search)
                            ->orWhere('name', 'like', '%' . $search . '%')
                            ->pluck('id')
                            ->toArray();

                        $departments = Department::where('name', $search)
                            ->orWhere('name', 'like', '%' . $search . '%')
                            ->pluck('id')
                            ->toArray();

                        $users->where(function ($query) use ($search) {
                            $query->where('last_name', 'like', '%' . $search . '%')
                                ->orWhere('first_name', 'like', '%' . $search . '%')
                                ->orWhere('patronymic', 'like', '%' . $search . '%')
                                ->orWhere(DB::raw("CONCAT(last_name, ' ', first_name, ' ', patronymic)"), 'LIKE', "%" . $search . "%");
                        });

                        if (!empty($groups)) {
                            $users->orWhere(function ($query) use ($groups) {
                                $query->orWhereIn('users.group_id', $groups);
                            });
                        }

                        if (!empty($departments)) {
                            $users->orWhere(function ($query) use ($departments) {
                                $query->orWhereIn('users.department_id', $departments);
                            });
                        }

                        $work_volumes->whereHas('made_task.responsible_user', function ($q) use ($users) {
                            $q->whereIn('id', $users->pluck('id')->toArray());
                        });
                    } else if ($iter == 'projects.entity') {
                        $search = mb_strtolower($values[$key]);
                        $result = array_filter(Project::$entities, function($item) use ($search) {
                            return stristr(mb_strtolower($item), $search);
                        });

                        $work_volumes->whereIn($iter, array_keys($result));
                    }
                } elseif ($iter == 'material') {
                    $mat_names[] = $values[$key];
                } else {
                    $work_volumes->where($iter, 'like', '%' . $values[$key] . '%');
                }
            }

            if ($mat_names) {
                $work_volumes->where(function ($query) use ($mat_names) {
                    foreach ($mat_names as $name) {
                        $query->orWhereHas('materials', function ($q) use ($name) {
                            $q->whereHasMorph('manual', ['App\Models\Manual\ManualMaterial'], function($mat) use ($name) {
                                $mat->where('name', 'like', '%' . $name . '%')
                                    ->where('material_type', 'regular');
                            });
                    });
                    }
                });
            }
        }

        return view('work_volumes.index', [
            'work_volumes' => $work_volumes->paginate(20),
            'entities' => Project::$entities,
            'type' => WorkVolume::getModel()->wv_type,
            'old_request' => isset($old_request) ? $old_request : [],
            'material_names' => $material_names,
        ]);
    }
}
