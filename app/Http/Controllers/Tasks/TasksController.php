<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequests\TaskCreateRequest;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Contract\Contract;
use App\Models\Contractors\Contractor;
use App\Models\FileEntry;
use App\Models\Group;
use App\Models\Notification\Notification;
use App\Models\Project;
use App\Models\SupportMail;
use App\Models\Task;
use App\Models\TaskFile;
use App\Models\TaskRedirect;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use App\Notifications\Task\StandardTaskCreationNotice;
use App\Services\Commerce\ProjectDashboardService;
use App\Services\Tasks\Reports\TasksXLSXReport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use ReflectionClass;
use Telegram\Bot\Laravel\Facades\Telegram;

class TasksController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->can('dashbord') && ! Auth::user()->can('tasks')) {
            return abort(403);
        }

        $tasks = Task::query()
            ->where(function ($query) {
                $query->orWhere('tasks.is_solved', 0);

                $query->orWhere(function ($query) {
                    $query->whereNotNull('tasks.revive_at')
                        ->where(DB::Raw('date(tasks.revive_at)'), '>', DB::Raw('date(now())'));
                });
            })
            ->where('tasks.responsible_user_id', Auth::user()->id)
            ->leftJoin('users', 'users.id', '=', 'tasks.user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftJoin('project_objects', 'project_objects.id', 'projects.object_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->select(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) AS full_name'),
                'projects.name as project_name', 'project_objects.address as project_address', 'contractors.short_name as contractor_name', 'tasks.*');

        if ($request->search) {
            $tasks->getModel()->smartSearch($tasks, [
                'full_name',
                'tasks.name',
                'projects.name',
                'project_objects.address',
                'contractors.short_name',
            ], $request->search);
        }

        if ($request->has('name')) {
            $tasks = ($request->get('name') == 'asc') ? $tasks->orderBy('tasks.name') : $tasks->orderByDesc('tasks.name');
        } elseif ($request->has('date')) {
            $tasks = $request->get('date') == 'asc' ? $tasks->orderBy('tasks.expired_at') : $tasks->orderByDesc('tasks.expired_at');
        } else {
            //default sorting
            $tasks->orderByDesc(DB::Raw('ISNULL(`tasks`.`revive_at`)'));
            $tasks->orderByDesc('tasks.revive_at');
            $tasks->orderBy('tasks.is_seen');
            $tasks->orderByDesc('tasks.updated_at');
        }

        if (Auth::user()->can('dashbord')) {
            $data = [
                'tasks' => $tasks->paginate(10),
                'projects' => Project::all(),
                'work_volumes' => WorkVolume::has('tasks')
                    ->where('status', '!=', 3)
                    ->select('status', 'type')
                    ->get(),
                'offers' => CommercialOffer::whereIn('commercial_offers.id', [DB::raw('select max(commercial_offers.id) from commercial_offers GROUP BY project_id, is_tongue')])
                    ->where('status', '!=', 3)
                    ->where('is_tongue', '!=', 2)
                    ->select('status', 'is_tongue', 'id')
                    ->get(),
                'contracts' => Contract::whereIn('id', [DB::raw('select max(id) from contracts GROUP BY contract_id, type')])
                    ->where('status', '!=', 3)
                    ->select('status', 'contract_id', 'type')
                    ->get(),
            ];
        } else {
            $data = [
                'tasks' => $tasks->paginate(10),
            ];
        }

        if (Auth::user()->can('dashboard_smr')) {
            $important_projects = Project::with(['object', 'contractor', 'com_offers'])
                ->whereHas('object', function ($q) {
                    $q->orderBy('updated_at', 'desc');
                    $q->whereNotNull('short_name');
                })
                ->limit(8)
                ->get();

            $proj_stats = collect();
            foreach ($important_projects as $project) {
                $proj_stats->push((new ProjectDashboardService())->collectStats($project));
            }
            $data['proj_stats'] = $proj_stats;
        }

        //        dd($data['work_volumes']->where('status', 1)->groupBy('project_id'));
        //dd($data);
        return view('tasks.index', $data);
    }

    public function store(TaskCreateRequest $request)
    {
        if (((Auth::user()->can('tasks_default_myself') && $request->responsible_user_id == Auth::user()->id)) or Auth::user()->is_su) {
        } elseif ((Auth::user()->can('tasks_default_others') && $request->responsible_user_id != Auth::user()->id)) {
        } else {
            return abort(403);
        }

        DB::beginTransaction();

        $task = new Task();

        $task->name = $request->name;
        $task->description = $request->description;
        $task->project_id = $request->project_id;
        $task->status = 1;
        $task->contractor_id = $request->contractor_id;
        $task->user_id = Auth::user()->id;
        $task->responsible_user_id = $request->responsible_user_id;
        $task->expired_at = new Carbon($request->expired_at);

        $task->save();

        if ($request->documents) {
            foreach ($request->documents as $document) {
                $file = new TaskFile();

                $mime = $document->getClientOriginalExtension();
                $file_name = 'task-'.$task->id.'/task_files-'.uniqid().'.'.$mime;

                Storage::disk('task_files')->put($file_name, File::get($document));

                FileEntry::create([
                    'filename' => $file_name,
                    'size' => $document->getSize(),
                    'mime' => $document->getClientMimeType(),
                    'original_filename' => $document->getClientOriginalName(),
                    'user_id' => Auth::user()->id,
                ]);

                $file->file_name = $file_name;
                $file->is_final = 0;
                $file->task_id = $task->id;
                $file->user_id = Auth::user()->id;
                $file->original_name = $document->getClientOriginalName();

                $file->save();
            }
        }
        DB::commit();

        StandardTaskCreationNotice::send(
            $task->responsible_user_id,
            [
                'name' => $task->name,
                'additional_info' => ' Ссылка на задачу: ',
                'url' => $task->task_route(),
                'task_id' => $task->id,
                'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                'project_id' => $task->project_id ? $task->project_id : null,
                'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
            ]
        );
        $task->refresh();

        if ($request->from_project) {
            return redirect()->back();
        }

        return redirect()->route('tasks::index');
    }

    public function get_projects(Request $request)
    {
        $projects = Project::getAllProjects();

        if ($request->q) {
            $projects = $projects->where('projects.name', 'like', '%'.$request->q.'%')
                ->orWhere('contractors.short_name', 'like', "%{$request->q}%")
                ->orWhere('project_objects.address', 'like', '%'.$request->q.'%');
        }

        $projects_found_count = $projects->count();

        $projects = $projects->take(10)->get();

        $results[] = [
            'id' => '',
            'text' => 'Показано '.($projects_found_count < 10 ? $projects_found_count : 10).' из '.$projects_found_count.' найденных',
            'disabled' => true,
        ];
        foreach ($projects as $project) {
            $results[] = [
                'id' => $project->id,
                'text' => 'Контрагент: '.$project->contractor_name
                    .'. Адрес: '.mb_strimwidth($project->project_address, 0, 50, '...')
                //                    . ' "' . mb_strimwidth($project->name, 0, 40) . '"' //in case you also need project name. but it becomes a really mess with it.
                ,
            ];
        }

        return ['results' => $results];
    }

    public function get_users(Request $request)
    {
        $users = User::getAllUsers()->where('status', 1);

        if ($request->groupConstantName) {
            $groupModelConstants = (new ReflectionClass(new Group))->getConstants();
            $users = $users->whereIn('group_id', $groupModelConstants[$request->groupConstantName]);
        }

        if ($request->q) {
            $groups = Group::where('name', $request->q)
                ->orWhere('name', 'like', '%'.$request->q.'%')
                ->pluck('id')
                ->toArray();

            $users = $users->where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like', '%'.$request->q.'%')
                ->orWhere(DB::raw('CONCAT(last_name, " ", first_name)'), 'like', '%'.$request->q.'%')
                ->orWhere('users.id', $request->q);

            if (! empty($groups)) {
                $users = $users->orWhereIn('group_id', [$groups]);
            }
        }

        $users = $users->whereNotIn('users.id', [1, $request->not ? intval($request->not) : 0])->take(6)->get();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user->id,
                'text' => trim($user->last_name.' '.$user->first_name.' '.$user->patronymic).', '.$user->group_name,
            ];
        }

        return ['results' => $results];
    }

    public function get_responsible_users(Request $request, $id)
    {
        $users = User::getAllUsers()
            ->where('status', 1)
            ->where('users.id', '!=', $id);

        if ($request->q) {
            $groups = Group::where('name', $request->q)
                ->orWhere('name', 'like', '%'.$request->q.'%')
                ->pluck('id')
                ->toArray();

            $users = $users->where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like', '%'.$request->q.'%');

            if (! empty($groups)) {
                $users = $users->orWhereIn('group_id', [$groups]);
            }
        }

        $users = $users->take(6)->get();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user->id,
                'text' => $user->full_name.', Должность: '.$user->group_name,
            ];
        }

        return ['results' => $results];
    }

    public function get_contractors(Request $request)
    {
        $contractors = Contractor::query();

        if ($request->q) {
            $contractors = $contractors->where('full_name', 'like', '%'.trim($request->q).'%')
                ->orWhere('short_name', 'like', '%'.trim($request->q).'%')
                ->orWhere('inn', 'like', '%'.trim($request->q).'%')
                ->orWhere('kpp', 'like', '%'.trim($request->q).'%');
        }

        $contractors = $contractors->where('in_archive', 0)->take(6)->get();

        $results = [];
        foreach ($contractors as $contractor) {
            $results[] = [
                'id' => $contractor->id,
                'text' => $contractor->short_name.', ИНН: '.$contractor->inn,
            ];
        }

        return ['results' => $results];
    }

    public function card($id): View
    {
        $task = Task::where('tasks.id', $id)
            ->leftJoin('users', 'users.id', '=', 'tasks.user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftJoin('project_objects', 'project_objects.id', 'projects.object_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')->with('user')
            ->select(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) AS full_name'),
                'projects.name as project_name', 'contractors.short_name as contractor_name',
                'project_objects.address as project_address', 'project_objects.name as object_name', 'tasks.*')
            ->firstOrfail();

        if ($task->status != 1) {
            abort(404);
        }

        // now we can see other's solved tasks
        //if ($task->is_solved == 1) {
        //abort(403);
        //}

        $task->is_seen = 1;

        Notification::where('task_id', $task->id)
            ->where('name', $task->name)
            ->update(['is_seen' => 1]);

        $task->save();

        $task_files = TaskFile::where('task_files.task_id', $id)
            ->leftJoin('users', 'users.id', '=', 'task_files.user_id')
            ->select(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) AS full_name'), 'task_files.*');

        if ($this->isWorkAgreementTask($task)) {
            $ticket_files = SupportMail::findOrFail($task->target_id)->files;
        }

        $task_redirects = TaskRedirect::where('task_id', $id);

        $users_redirects = User::select('users.id', DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic) AS full_name'))
            ->whereIn('users.id', $task_redirects->pluck('old_user_id'))
            ->orWhereIn('users.id', $task_redirects->pluck('responsible_user_id'));

        return view('tasks.card', [
            'task' => $task,
            'task_files' => $task_files->get(),
            'task_redirects' => $task_redirects->get(),
            'users_redirects' => $users_redirects->get(),
            'ticket_files' => $ticket_files ?? null,
        ]);
    }

    public function solve(Request $request, $id): RedirectResponse
    {
        $task = Task::findOrFail($id);

        $task->final_note = $request->final_note;
        $task->is_solved = 1;

        if ($request->documents) {
            foreach ($request->documents as $document) {
                $file = new TaskFile();

                $mime = $document->getClientOriginalExtension();
                $file_name = 'task-'.$task->id.'/task_files-'.uniqid().'.'.$mime;

                Storage::disk('task_files')->put($file_name, File::get($document));

                FileEntry::create([
                    'filename' => $file_name,
                    'size' => $document->getSize(),
                    'mime' => $document->getClientMimeType(),
                    'original_filename' => $document->getClientOriginalName(),
                    'user_id' => Auth::user()->id,
                ]);

                $file->file_name = $file_name;
                $file->is_final = 1;
                $file->task_id = $task->id;
                $file->user_id = Auth::user()->id;
                $file->original_name = $document->getClientOriginalName();

                $file->save();
            }
        }

        $task->save();

        return redirect()->route('tasks::index');
    }

    public function update_resp_user(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        if (((Auth::user()->can('tasks_default_others') && $request->responsible_user_id != Auth::user()->id)) or Auth::user()->is_su) {
        } elseif ((Auth::user()->can('tasks_default_myself') && $request->responsible_user_id == Auth::user()->id)) {
        } else {
            return abort(403);
        }

        $task_redirect = new TaskRedirect();

        $task_redirect->old_user_id = $task->responsible_user_id;

        $task->responsible_user_id = $request->responsible_user_id;

        $task_redirect->task_id = $id;
        $task_redirect->redirect_note = $request->redirect_note;
        $task_redirect->responsible_user_id = $request->responsible_user_id;

        $task_redirect->save();
        $task->save();

        return redirect()->route('tasks::index');
    }

    public function redirect(): RedirectResponse
    {
        if (! (Auth::user()->can('tasks') || Auth::user()->can('dashbord'))) {
            return redirect()->route('notifications::index');
        }

        return redirect()->route('tasks::index');
    }

    public function error(): View
    {
        if (session()->has('errors')) {
            return view('errors.custom_error');
        }

        abort(404);
    }

    public function updatedActivity()
    {
        $activity = Telegram::getUpdates();

        dd($activity);
    }

    public function isWorkAgreementTask($task): bool
    {
        return $task->target_id and strpos($task->name, 'дополнительных работ');
    }

    public function searchProjects(Request $request)
    {
        $important_projects = Project::with(['object', 'contractor', 'com_offers']);
        //            ->whereHas('com_offers', function ($q) {
        //                $q->whereIn('status', [4, 5]);
        //            });
        if ($request->search) {
            $important_projects = $important_projects->where(function ($query) use ($request) {
                $query->orWhereHas('object', function ($q) use ($request) {
                    $q->where('short_name', 'like', '%'.$request->search.'%');
                })
                    ->orWhereHas('contractor', function ($q) use ($request) {
                        $q->where('short_name', 'like', '%'.$request->search.'%');
                    });
            });

        } else {
            $important_projects = $important_projects->whereHas('object', function ($q) {
                $q->orderBy('updated_at', 'desc');
                $q->whereNotNull('short_name');
            });
        }

        $important_projects = $important_projects->limit(8)
            ->get();

        $proj_stats = collect();
        foreach ($important_projects as $project) {
            $proj_stats->push((new ProjectDashboardService())->collectStats($project));
        }

        return $proj_stats;
    }

    public function showTasksReportFilterForm(Request $request): View
    {
        return view('tasks.filter-tasks-report');
    }

    public function downloadTasksReport(Request $request)
    {
        $filterOptions = json_decode($request->input('filterOptions'));
        $reportType = json_decode($request->input('reportType'))->reportType;

        if ($reportType == 'tasks') {
            $tasks = (new Task)
                ->dxLoadOptions($filterOptions)
                ->leftJoin('users', 'users.id', '=', 'tasks.user_id')
                ->leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
                ->leftJoin('project_objects', 'project_objects.id', '=', 'projects.object_id')
                ->leftJoin('contractors', 'contractors.id', '=', 'tasks.contractor_id')
                ->leftJoin('commercial_offers', 'commercial_offers.project_id', '=', 'projects.id')
                ->leftJoin('commercial_offer_material_splits', 'commercial_offer_material_splits.com_offer_id', '=', 'commercial_offers.id')
                ->leftJoin('manual_materials', 'commercial_offer_material_splits.man_mat_id', '=', 'manual_materials.id')
                ->leftJoin('work_volume_material_complects', 'commercial_offer_material_splits.man_mat_id', '=', 'work_volume_material_complects.id')
                ->addSelect([
                    'tasks.id',
                    'tasks.id as task_id',
                    'projects.name AS project_name',
                    'project_objects.address AS project_address',
                    'contractors.short_name AS contractor_name',
                    DB::Raw('IFNULL(`tasks`.`final_note`, (SELECT `final_note` FROM `tasks` AS `prev_task` WHERE `prev_task`.`id` = `tasks`.`prev_task_id`)) as final_note'),
                    'tasks.revive_at',
                ])
                ->where(function ($query) {
                    $query->orWhere('tasks.is_solved', 0);

                    $query->orWhere(function ($query) {
                        $query->whereNotNull('tasks.revive_at')
                            ->where(DB::Raw('date(tasks.revive_at)'), '>', DB::Raw('date(now())'));
                    });
                })
                // ->where('tasks.responsible_user_id', '=', Auth::user()->id)
                ->where('commercial_offers.status', '=', 5)
                ->orderByDesc(DB::Raw('ISNULL(`tasks`.`revive_at`)'))
                ->orderByDesc('tasks.revive_at')
                ->orderBy('project_objects.address')
                ->distinct()
                ->get()
                ->toArray();
        } elseif ($reportType == 'tasksAndMaterials') {
            $tasks = (new Task)
                ->dxLoadOptions($filterOptions)
                ->leftJoin('users', 'users.id', '=', 'tasks.user_id')
                ->leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
                ->leftJoin('project_objects', 'project_objects.id', '=', 'projects.object_id')
                ->leftJoin('contractors', 'contractors.id', '=', 'tasks.contractor_id')
                ->leftJoin('commercial_offers', 'commercial_offers.project_id', '=', 'projects.id')
                ->leftJoin('commercial_offer_material_splits', 'commercial_offer_material_splits.com_offer_id', '=', 'commercial_offers.id')
                ->leftJoin('manual_materials', 'commercial_offer_material_splits.man_mat_id', '=', 'manual_materials.id')
                ->leftJoin('work_volume_material_complects', 'commercial_offer_material_splits.man_mat_id', '=', 'work_volume_material_complects.id')
                ->addSelect([
                    'tasks.id',
                    'tasks.id as task_id',
                    'projects.name AS project_name',
                    'project_objects.address AS project_address',
                    'contractors.short_name AS contractor_name',
                    DB::Raw('IFNULL(`tasks`.`final_note`, (SELECT `final_note` FROM `tasks` AS `prev_task` WHERE `prev_task`.`id` = `tasks`.`prev_task_id`)) as final_note'),
                    'tasks.revive_at',
                    'commercial_offers.id AS commercial_offers_id',
                    'commercial_offers.option AS commercial_offers_title',
                    DB::Raw("CASE `commercial_offer_material_splits`.`material_type` WHEN 'regular' THEN `manual_materials`.`name` WHEN 'complect' THEN `work_volume_material_complects`.`name` END AS `material_name`"),
                    'commercial_offer_material_splits.count AS material_count',
                ])
                ->where(function ($query) {
                    $query->orWhere('tasks.is_solved', 0);

                    $query->orWhere(function ($query) {
                        $query->whereNotNull('tasks.revive_at')
                            ->where(DB::Raw('date(tasks.revive_at)'), '>', DB::Raw('date(now())'));
                    });
                })
                // ->where('tasks.responsible_user_id', '=', Auth::user()->id)
                ->where('commercial_offers.status', '=', 5)
                ->orderByDesc(DB::Raw('ISNULL(`tasks`.`revive_at`)'))
                ->orderByDesc('tasks.revive_at')
                ->orderBy('tasks.id')
                ->orderBy('project_objects.address')
                ->orderBy('commercial_offers.title')
                ->orderBy('manual_materials.name')
                ->distinct()
                ->get()
                ->groupBy(['id', 'commercial_offers_id'])
                ->toArray();
        }

        return (new TasksXLSXReport($tasks, $reportType))->export();
    }

    public function currentUserTasksProjectObjectsList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new Task)->dxLoadOptions($options)
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->join('project_objects', 'project_objects.id', '=', 'projects.object_id')
            // ->where('tasks.responsible_user_id', '=', Auth::id())
            ->where('tasks.is_solved', '=', 0)
            ->orderBy('project_objects.name')
            ->distinct()
            ->get(['project_objects.id', 'project_objects.name as project_object_name', 'project_objects.short_name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function currentUserTasksContractorsList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new Task)->dxLoadOptions($options)
            ->join('contractors', 'contractors.id', '=', 'tasks.contractor_id')
            // ->where('tasks.responsible_user_id', '=', Auth::id())
            ->where('tasks.is_solved', '=', 0)
            ->orderBy('short_name')
            ->distinct()
            ->get(['contractors.id', 'contractors.short_name'])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }

    public function currentUserTasksSplitMaterialList(Request $request)
    {
        $options = json_decode($request['data']);

        return (new Task)->dxLoadOptions($options)
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->join('commercial_offers', 'commercial_offers.project_id', '=', 'tasks.project_id')
            ->leftJoin('commercial_offer_material_splits', 'commercial_offer_material_splits.com_offer_id', '=', 'commercial_offers.id')
            ->leftJoin('manual_materials', 'commercial_offer_material_splits.man_mat_id', '=', 'manual_materials.id')
            ->leftJoin('work_volume_material_complects', 'commercial_offer_material_splits.man_mat_id', '=', 'work_volume_material_complects.id')
            // ->where('tasks.responsible_user_id', '=', Auth::id())
            ->where('tasks.is_solved', '=', 0)
            ->whereNotNull(DB::Raw("CASE `commercial_offer_material_splits`.`material_type` WHEN 'regular' THEN `manual_materials`.`name` WHEN 'complect' THEN `work_volume_material_complects`.`name` END"))
            ->orderBy(DB::Raw("CASE `commercial_offer_material_splits`.`material_type` WHEN 'regular' THEN `manual_materials`.`name` WHEN 'complect' THEN `work_volume_material_complects`.`name` END"))
            ->distinct()
            ->get(['commercial_offer_material_splits.man_mat_id', DB::Raw("CASE `commercial_offer_material_splits`.`material_type` WHEN 'regular' THEN `manual_materials`.`name` WHEN 'complect' THEN `work_volume_material_complects`.`name` END AS `material_name`")])
            ->toJson(JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    }
}
