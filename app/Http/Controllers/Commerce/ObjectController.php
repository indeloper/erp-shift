<?php

namespace App\Http\Controllers\Commerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\ProjectObject;
use App\Models\Project;
use App\Models\Building\ObjectResponsibleUser;

use App\Http\Requests\ObjectRequests\ObjectRequest;


class ObjectController extends Controller
{


    public function index(Request $request)
    {
        $objects = ProjectObject::with('resp_users.user')
            ->with('material_accounting_type')
            ->orderBy('id', 'desc');

        if(Auth::user()->hasLimitMode(0)) {
            $objects->whereHas('resp_users', function ($users) {
                return $users->where('user_id', Auth::id());
            });
        }

        if ($request->search) {
            $objects->getModel()->smartSearch($objects,
                [
                    'name',
                    'address',
                    'short_name',
                    'cadastral_number'
                ],
                $request->search
            );
        }

        $objects_ids = $objects->paginate(15)->pluck('id');

        $projects = Project::getAllProjects()->whereIn('projects.object_id', $objects_ids);

        return view('objects.index', [
            'objects' => $objects->paginate(15),
            'projects' => $projects->get()
        ]);
    }


    public function store(ObjectRequest $request)
    {
        $object = new ProjectObject();

        $object->name = $request->name;
        $object->address = $request->address;
        $object->cadastral_number = $request->cadastral_number;
        $object->short_name = $request->short_name;
        $object->material_accounting_type = $request->material_accounting_type;
        if ($object->is_participates_in_material_accounting) {
            $object->is_participates_in_material_accounting = $request->is_participates_in_material_accounting;
        } else {
            $object->is_participates_in_material_accounting = 0;
        }


        $object->save();

        if($request->task_id) {
            return \GuzzleHttp\json_encode($object->id);
        }

        return redirect()->route('objects::index');
    }


    public function update(ObjectRequest $request)
    {
        DB::beginTransaction();

        $object = ProjectObject::findOrFail($request->object_id);

        $object->name = $request->name;
        $object->address = $request->address;
        $object->cadastral_number = $request->cadastral_number;
        $object->short_name = $request->short_name;
        $object->material_accounting_type = $request->material_accounting_type;
        if (isset($request->is_participates_in_material_accounting)) {
            $object->is_participates_in_material_accounting = 1;
        } else {
            $object->is_participates_in_material_accounting = 0;
        }

        $object->save();

        if(Auth::user()->isProjectManager() or Auth::user()->isInGroup(43)/*8*/) {
            ObjectResponsibleUser::where('object_id', $request->object_id)->where('role', 1)->delete();

            if ($request->resp_user_role_one) {
                foreach ($request->resp_user_role_one as $user_id) {
                    ObjectResponsibleUser::create([
                        'object_id' => $request->object_id,
                        'user_id' => $user_id,
                        'role' => 1
                    ]);
                }
            }
        }

        DB::commit();

        return redirect()->route('objects::index');
    }

    public function get_object_projects()
    {
        $projects = Project::where('object_id', request()->object_id)->get();

        $results = [];
        foreach ($projects as $project) {
            $results[] = [
                'name' => $project->name,
                'link' => route('projects::card', $project->id),
            ];
        }
        return \GuzzleHttp\json_encode($results);
    }

    public function getObjects(Request $request)
    {
        $objects = ProjectObject::query();
        if ($request->q) {
            $objects = $objects->where(function ($objects) use ($request) {
                $objects->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('address', 'like', '%' . $request->q . '%')
                    ->orWhere('short_name', 'like', '%' . $request->q . '%');
            });
        }
        $objects = $objects->take(10)->get();
        return $objects->map(function ($object) {
            return ['code' => $object->id . '', 'label' => $object->address];
        });
    }
}
