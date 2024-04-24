<?php

namespace App\Http\Controllers\Commerce;

use App\Domain\Enum\NotificationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContractorRequests\ContractorContactRequest;
use App\Http\Requests\ProjectRequest\{
    ProjectRequest,
    ProjectTimeResponsibleUserRequest,
    SelectResponsibleUserRequest,
    UserProjectAppointRequest,
    UserProjectDetachRequest};
use App\Models\Building\ObjectResponsibleUser;
use App\Models\Building\ObjectResponsibleUserRole;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\CommercialOffer\CommercialOfferMaterialSplit;
use App\Models\CommercialOffer\CommercialOfferRequest;
use App\Models\Contract\Contract;
use App\Models\Contract\ContractRequest;
use App\Models\Contractors\{Contractor, ContractorContact, ContractorContactPhone};
use App\Models\ExtraDocument;
use App\Models\Group;
use App\Models\Project;
use App\Models\ProjectContact;
use App\Models\ProjectContractors;
use App\Models\ProjectContractorsChangeHistory;
use App\Models\ProjectDocument;
use App\Models\ProjectObject;
use App\Models\ProjectResponsibleUser;
use App\Models\Task;
use App\Models\TaskFile;
use App\Models\TaskRedirect;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use App\Models\WorkVolume\WorkVolumeMaterial;
use App\Models\WorkVolume\WorkVolumeRequest;
use App\Traits\TimeCalculator;
use App\Traits\UserSearchByGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProjectController extends Controller
{
    use UserSearchByGroup;
    use TimeCalculator;

    public function index(Request $request)
    {
        $projects = Project::getAllProjects()
            ->with('last_task', 'last_task.redirects', 'last_task.task_files', 'last_task.responsible_user',
                'work_volumes', 'com_offers', 'contracts.commercial_offers');

        if ($request->material_names) {
            $filter = explode(',' , $request->material_names) ?? [];

            $projects = $projects->MaterialFilter($filter);
        }

        if ($request->search) {
            $search = mb_strtolower($request->search);
            $result = array_filter($projects->getModel()->project_status, function($item) use ($search) {
                return stristr(mb_strtolower($item), $search);
            });

            $entity = array_filter($projects->getModel()::$entities, function($item) use ($search) {
                return stristr(mb_strtolower($item), $search);
            });

            $projects = $projects->where(function($q) use ($request, $result, $entity) {
                $q->where('projects.name', 'like', '%' . $request->search . '%')
                    ->orWhere('project_objects.address', 'like', '%' . $request->search . '%')
                    ->orWhere('project_objects.short_name', 'like', '%' . $request->search . '%')
                    ->orWhere(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic)'), 'like', '%' . $request->search . '%')
                    ->orWhere(DB::raw('CONCAT(contractors.short_name, " ", contractors.inn)'), 'like', '%' . $request->search . '%')
                    ->orWhereIn('projects.status', array_keys($result))
                    ->orWhereIn('projects.entity', array_keys($entity));
            });
        }

        if ($request->status) {
            $projects->where('projects.status', $request->status);
        }

        return view('projects.index', [
            'projects' => $projects->paginate(20),
            'material_names' => $request->material_names,
        ]);
    }


    public function create(Request $request)
    {
        if ($request->contractor_id) {
            $contractor = Contractor::findOrFail($request->contractor_id);
        }

        return view('projects.create', [
            'contractor' => $request->contractor_id ? $contractor : '',
            'entities' => Project::$entities
        ]);
    }


    public function store(ProjectRequest $request)
    {
        DB::beginTransaction();

        $project = new Project();

        // now we have array of contractors with one main
        $project->contractor_id = $request->contractor_ids[array_search($request->main_contractor, $request->contractor_ids)];
        $project->name = $request->name;
        $project->object_id = $request->object_id;
        $project->description = $request->description;
        $project->entity = $request->entity;
        $project->status = 1;
        $project->is_tongue = in_array('is_tongue', $request->type);
        $project->is_pile = in_array('is_pile', $request->type);
        $project->user_id = Auth::id();

        $project->save();

        // create relations for other contractors
        $other_contractors = array_diff($request->contractor_ids, [$request->main_contractor]);
        foreach ($other_contractors as $contractor_id) {
            ProjectContractors::create([
                'project_id' => $project->id,
                'contractor_id' => $contractor_id,
                'user_id' => Auth::id(),
            ]);

            ProjectContractorsChangeHistory::create([
                'new_contractor_id' => $contractor_id,
                'project_id' => $project->id,
                'user_id' => Auth::id(),
            ]);
        }

        DB::commit();

        if ($request->has('contractor_contact_ids'))
            return $this->updateContacts($request->contractor_contact_ids, $request->project_contact_ids, $project->contractor_id, $project->id);

        if($request->task_id) {
            return redirect()->route('tasks::new_call', [
                $request->task_id,
                'project_id' => $project->id,
                'contractor_id' => $request->contractor_id,
                'contact_id' => $request->contact_id
            ]);
        }

        return redirect()->route('projects::card', $project->id);
    }

    public function users(Project $project)
    {
        $projectTimeResp = $project->timeResponsible->id ?? -1;
        $isTimeResponsible = auth()->id() == $projectTimeResp;
        return view('human_resources.users_wrapper', [
            'data' => [
                'can_add_user' => $isTimeResponsible,
                'project' => $project,
                'project_users' => array_reverse($project->allUsers()->toArray()),
                'source' => 'project',
            ]
        ]);
    }

    public function card(Request $request, $id)
    {
        $project = Project::with('contractors.contractor')->findOrFail($id);

        $solved_tasks = Task::orderBy('created_at', 'desc')
            ->with('responsible_user', 'author', 'user')
            ->where('tasks.project_id', $id)
            ->leftJoin('users', 'users.id', '=', 'tasks.responsible_user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->leftjoin('work_volumes', 'tasks.target_id', 'work_volumes.id')
            ->leftjoin('project_objects', 'project_objects.id', 'projects.object_id')
            ->select('users.last_name', 'users.first_name', 'users.patronymic', 'projects.name as project_name',
                'contractors.short_name as contractor_name', 'work_volumes.type', 'work_volumes.id as work_volume_id',
                'project_objects.address as object_address', 'tasks.*')
            ->take(6)->get();

        $object = ProjectObject::findOrFail($project->object_id);

        $creater = User::getAllUsers()->where('users.id', $project->user_id)->first();

        $p_contacts = ProjectContact::where('project_id', $id)->pluck('contact_id');
        $p_users = ProjectResponsibleUser::where('project_id', $id)->pluck('user_id')->toArray();

        $contacts = ContractorContact::with('phones')->whereIn('contractor_contacts.id', $p_contacts)
            ->leftJoin('project_contacts', function($leftJoin) use ($id)
                {
                    $leftJoin->on('contractor_contacts.id', '=', 'project_contacts.contact_id');
                    $leftJoin->on(DB::raw('project_contacts.project_id'), DB::raw('='),DB::raw("'".$id."'"));
                })
            ->select('contractor_contacts.*', 'project_contacts.id as proj_contact_id', 'project_contacts.note as proj_contact_note')
            ->get();
        foreach ($contacts as $contact) {
            foreach ($contact->phones as $phone) {
                preg_match("/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{0,2})$/", $phone->phone_number, $matches);
                if(count($matches) > 2) {
                    $phone->phone_number = '+' . $matches[1] . ' (' . $matches[2] . ') ' . implode(array_slice(array_filter($matches), 3, 3), '-');
                }
            }
        }

        $contractor = Contractor::findOrFail($project->contractor_id);
        foreach ($contractor->phones as $phone) {
            preg_match("/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{0,2})$/", $phone->phone_number,  $matches);
            if(count($matches) > 2) {
                $phone->phone_number = '+' . $matches[1] . ' (' . $matches[2] . ') ' . implode(array_slice(array_filter($matches), 3, 3), '-');
            }
        }

        $resp_users = User::getAllUsers()->whereIn('users.id', $p_users)
            //->where('users.id', '!=', $creater->id)
            ->leftJoin('project_responsible_users', function($leftJoin)use($id)
                {
                    $leftJoin->on('users.id', '=', 'project_responsible_users.user_id');
                    $leftJoin->on(DB::raw('project_responsible_users.project_id'), DB::raw('='),DB::raw("'".$id."'"));
                })
            ->select('users.id', 'users.last_name', 'users.first_name', 'users.patronymic', 'users.birthday',
                'users.email', 'users.person_phone', 'users.work_phone', 'users.status', 'departments.name as dep_name',
                'groups.name as group_name', 'project_responsible_users.id as resp_user_id', 'project_responsible_users.role as role', 'project_responsible_users.user_id as user_id')
            ->get();

        $project_docs = ProjectDocument::where('project_id', $id)
            ->leftjoin('users', 'users.id', '=', 'project_documents.user_id')
            ->select('project_documents.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->get();

        $extra_documents = ExtraDocument::orderBy('version', 'desc')
            ->where('project_id', $id)
            ->leftjoin('users', 'users.id', '=', 'extra_documents.user_id')
            ->select('extra_documents.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->get();
//
//        $com_offers = CommercialOffer::where('project_id', $id)
//            ->leftjoin('users', 'users.id', '=', 'commercial_offers.user_id')
//            ->select('commercial_offers.*', 'users.last_name', 'users.first_name', 'users.patronymic')
//            ->get();

        // $extra_com_offers = ExtraCommercialOffer::orderBy('version', 'desc')
        //     ->where('project_id', $id)
        //     ->leftjoin('users', 'users.id', '=', 'extra_commercial_offers.user_id')
        //     ->select('extra_commercial_offers.*', 'users.last_name', 'users.first_name', 'users.patronymic')
        //     ->get();


        $task_files = TaskFile::whereIn('task_files.task_id', $solved_tasks->pluck('id'))
            ->leftJoin('users', 'users.id', '=', 'task_files.user_id')
            ->select('users.last_name', 'users.first_name', 'users.patronymic', 'task_files.*');

        $task_redirects = TaskRedirect::select('task_redirects.*', 'tasks.project_id')
            ->leftJoin('tasks', 'tasks.id', 'task_redirects.task_id')
            ->where('tasks.project_id', $id);

        $task_responsible_users = User::select('users.id', 'users.first_name', 'users.last_name', 'users.patronymic')
            ->whereIn('users.id', $solved_tasks->pluck('responsible_user_id'))
            ->orWhereIn('users.id', $task_redirects->take(6)->pluck('old_user_id'))
            ->orWhereIn('users.id', $task_redirects->take(6)->pluck('responsible_user_id'))
            ->orWhereIn('users.id', $solved_tasks->pluck('description'));

        $com_offers_task = CommercialOffer::whereIn('project_id', $solved_tasks->pluck('project_id'));

        $work_volumes = WorkVolume::where('project_id', $id)->where('type', '!=', 2)
            ->orderBy('work_volumes.version', 'desc')
            ->with('get_requests');

        $work_volume_requests = WorkVolumeRequest::where('project_id', $id)
            ->where('work_volume_requests.status', 0)
            ->leftJoin('users', 'users.id', '=', 'work_volume_requests.user_id')
            ->select('work_volume_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('files');

        $com_offers = CommercialOffer::where('project_id', $id)
            ->orderBy('commercial_offers.version', 'desc')
            ->leftjoin('users', 'users.id', '=', 'commercial_offers.user_id')
            ->select('commercial_offers.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->with('get_requests');

        foreach ($solved_tasks as $solved_task) {
            $com_for_tasks = CommercialOffer::where('project_id', $id)
                ->orderBy('commercial_offers.version', 'desc')
                ->leftjoin('users', 'users.id', '=', 'commercial_offers.user_id')
                ->select('commercial_offers.*', 'users.last_name', 'users.first_name', 'users.patronymic')
                ->whereIn('commercial_offers.status', [1, 2, 4, 5])->first();
            #$wv_for_tasks = WorkVolume::where('project_id', $id)->orderBy('work_volumes.version', 'desc')
                #->with('get_requests')->whereIn('work_volumes.status', [1, 2])->get();

            $solved_task->commercial_offer_id = is_null($com_for_tasks)?'':$com_for_tasks->id;
            #$solved_task->work_volume_id = is_null($wv_for_tasks)?'':$wv_for_tasks->id;
            $solved_task->commercial_offer_file = is_null($com_for_tasks)?'':$com_for_tasks->file_name;
        }

        $commercial_offer_requests = CommercialOfferRequest::where('project_id', $id)
            ->where('commercial_offer_requests.status', 0)
            ->leftJoin('users', 'users.id', '=', 'commercial_offer_requests.user_id')
            ->select('commercial_offer_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic');

        $COpile = ProjectResponsibleUser::where('project_id', $id)->where('role', 1)->first();
        $COtongue = ProjectResponsibleUser::where('project_id', $id)->where('role', 2)->first();

        $contracts = Contract::where('project_id', $id)->with('key_dates')
            ->leftJoin('users', 'users.id', '=', 'contracts.user_id')
            ->select('contracts.*', 'users.last_name', 'users.first_name', 'users.patronymic');

        $contractsLogic = Contract::where('project_id', $id)
            ->leftJoin('users', 'users.id', '=', 'contracts.user_id')
            ->select('contracts.*', 'users.last_name', 'users.first_name', 'users.patronymic')
            ->where('contracts.status', '>=', 4)->first();

        $contract_map = $contracts->get()->groupBy('contract_id');

        $contract_requests = ContractRequest::whereIn('contract_requests.contract_id', $contracts->get()->groupBy('id')->keys())
            ->leftJoin('contracts', 'contracts.id', '=', 'contract_requests.contract_id')
            ->leftJoin('users', 'users.id', '=', 'contract_requests.user_id')
            ->select('contract_requests.*', 'users.last_name', 'users.first_name', 'users.patronymic', 'contracts.name as contract_name', 'contracts.contract_id as contract_number')
            ->with('files');

        $contract_resp_user_ids = isset($resp_users->where('role', '7')->first()->id) ? $resp_users->where('role', '7')->pluck('id')->toArray() : [0];

        $respTongueKP = isset(ProjectResponsibleUser::where('project_id', $id)->where('role', 2)->first()->user_id) ? ProjectResponsibleUser::where('project_id', $id)->where('role', 2)->first()->user_id : 0;
        $respPileKP = isset(ProjectResponsibleUser::where('project_id', $id)->where('role', 1)->first()->user_id) ? ProjectResponsibleUser::where('project_id', $id)->where('role', 1)->first()->user_id : 0;

        $alwaysResp = [];
        $resp_groups = Group::whereIn('id', [5/*3*/, 8/*5*/, 50/*7*/, 53/*16*/, 6/*24*/, 54/*35*/])->get();
        foreach($resp_groups as $group) {
            $alwaysResp = array_merge($alwaysResp, $group->getUsers()->pluck('id')->toArray());
        }
        $all_resp_users = array_unique(array_merge($resp_users->pluck('user_id')->toArray(), $alwaysResp));
        $contractors = Contractor::where('in_archive', 0)->get();

        // for new agreeKP logic
        $agree_tasks = Task::where('project_id', $id)->where('status', 6)->where(function($q) {
            $q->orWhere('is_solved', 0)->orWhere('revive_at', '<>', null);
        })->where('responsible_user_id', Auth::id())->get();

        $task_to_show = Task::find($request->task);

        $work_volumes_options = WorkVolume::where('project_id', $id)
            ->where('type', '!=', 2)
            ->orderBy('version', 'asc')
            ->groupBy('type', 'option')
            ->select('work_volumes.*', DB::raw('max(version) as version'), DB::raw('max(id) as id'))
            ->get();

        $com_offers_options = CommercialOffer::where('project_id', $id)
            ->with('work_volume')
            ->whereHas('work_volume', function($q) {
                $q->where('status', 2);
            })
            ->whereIn('status', [1, 2, 3, 4, 5])
            ->orderBy('version', 'asc')
            ->groupBy('is_tongue', 'option')
            ->select('commercial_offers.*', DB::raw('max(version) as version'), DB::raw('max(id) as id'))
            ->get();

        $work_volumes_all = WorkVolume::where('project_id', $id)->where('type', '!=', 2)
            ->orderBy('work_volumes.version', 'desc')
            ->with('get_requests');

        return view('projects.card', [
            'project' => $project,
            'object' => $object,
            'contractor' => $contractor,
            'contacts' => $contacts->unique(),
            'resp_users' => $resp_users/*->unique()*/,
            'creater' => $creater,
            'project_docs' => $project_docs,
            'extra_documents' => $extra_documents,
            'com_offersForeach' => $com_offers->get()->sortByDesc('version')->groupBy(['is_tongue', 'option']),
            'com_offers' => $com_offers->get(),
            // 'extra_com_offers' => $extra_com_offers,
            'commercial_offer_requests' => $commercial_offer_requests->get(),
            'COpile' => $COpile,
            'COtongue' => $COtongue,
            'com_offers_options' => $com_offers_options,
            'solved_tasks' => $solved_tasks,
            'task_files' => $task_files->get(),
            'task_responsible_users' => $task_responsible_users->get(),
            'task_redirects' => $task_redirects->get(),
            'com_offers_task' => $com_offers_task,
            'work_volumesForeach' => $work_volumes->get()->sortByDesc('version')->groupBy(['type', 'option']),
            'work_volumes' => $work_volumes->orderBy('id', 'desc')->groupBy(['type', 'option'])->get(),
            'work_volumes_all' => $work_volumes_all->get(),
            'work_volumes_options' => $work_volumes_options,
            'work_volume_requests' => $work_volume_requests->get(),
            'contract_requests' => $contract_requests->get(),
            'contracts' => $contracts->orderBy('contract_id', 'desc')->orderBy('version', 'desc')->get(),
            'next_contract_id' => Contract::max('contract_id') + 1,
            'contract_map' => $contract_map,
            'contract_resp_user_ids' => $contract_resp_user_ids,
            'contractLogic' => $contractsLogic,
            'allRespUsers' => $all_resp_users,
            'contractors' => $contractors,
            'respTongueKP' => $respTongueKP,
            'respPileKP' => $respPileKP,
            'agree_tasks' => $agree_tasks,
            'task_to_show' => $task_to_show,
            'additional_contractors' => $project->contractors->count() ? $project->contractors : null,
        ]);
    }


    public function change_status(Request $request, $project_id)
    {
        DB::beginTransaction();

        $project = Project::findOrFail($project_id);
        $project->status = $request->new_status;
        $project->save();

        DB::commit();
        return \GuzzleHttp\json_encode(true);
    }


    public function select_contacts(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $this->authorize('edit', $project);

        $p_contact = ProjectContact::firstOrCreate(
            ['contact_id' => $request->contact_id, 'project_id' => $id]
        );
        $p_contact->note = $request->project_note;
        $p_contact->save();

        return redirect()->route('projects::card', $id)->with('contacts', 'Новый контакт добавлен');
    }


    public function get_contacts(Request $request, $contractor_id)
    {
        $contacts = ContractorContact::where('contractor_id', $contractor_id);

        $p_contacts = ProjectContact::where('project_id', $request->project_id)->pluck('contact_id');

        if ($request->q) {
            $contacts = $contacts->where('last_name', 'like', '%' . trim($request->q) . '%')
                ->orWhere('patronymic', 'like', '%' . trim($request->q) . '%')
                ->orWhere('position', 'like', '%' . trim($request->q) . '%')
                ->orWhere('position', 'like', '%' . trim($request->q) . '%')
                ->orWhere('patronymic', 'like', '%' . trim($request->q) . '%');
        }

        $contacts = $contacts->take(6)->get();

        $contacts = $contacts->whereNotIn('id', $p_contacts);
        $results = [];
        foreach ($contacts as $contact) {
            $results[] = [
                 'id' => $contact->id,
                 'text' => $contact->last_name . ' ' . $contact->first_name . ' ' . $contact->patronymic . ', Должность: ' . $contact->position,
             ];
        }

        return ['results' => $results];
    }

    public function get_contractors_contacts(Request $request)
    {
        $contacts = ContractorContact::whereIn('contractor_id', $request->contractor_ids)->whereNotIn('id', $request->contact_ids ?? []);

        $p_contacts = ProjectContact::where('project_id', $request->project_id)->pluck('contact_id');

        if ($request->q) {
            $contacts = $contacts->where('last_name', 'like', '%' . trim($request->q) . '%')
                ->orWhere('patronymic', 'like', '%' . trim($request->q) . '%')
                ->orWhere('position', 'like', '%' . trim($request->q) . '%')
                ->orWhere('position', 'like', '%' . trim($request->q) . '%')
                ->orWhere('patronymic', 'like', '%' . trim($request->q) . '%');
        }

        $contacts = $contacts->take(6)->get();

        $contacts = $contacts->whereNotIn('id', $p_contacts);
        $results = [];
        foreach ($contacts as $contact) {
            $results[] = [
                 'id' => $contact->id,
                 'text' => $contact->last_name . ' ' . $contact->first_name . ' ' . $contact->patronymic . ', Должность: ' . $contact->position,
             ];
        }

        return ['results' => $results];
    }


    public function get_objects(Request $request)
    {
        $objects = ProjectObject::query()->orderBy('id', 'desc');

        if ($request->q) {
            $objects = $objects->where('name', 'like', '%' . trim($request->q) . '%')
                ->orWhere('address', 'like', '%' . trim($request->q) . '%')
                ->orWhere('cadastral_number', 'like', '%' . trim($request->q) . '%');
        }

        $objects = $objects->take(6)->get();

        $results = [];
        if (Auth::user()->can('objects_create')) {
            if ($request->create_project == 1) {
                $results[] = [
                     'id' => 'create_object',
                     'text' => 'Новый объект',
                 ];
            }
        }
        foreach ($objects as $object) {
            $results[] = [
                 'id' => $object->id,
                 'text' => $object->name . '. Адрес: ' . $object->address,
             ];
        }

        return ['results' => $results];
    }


    public function get_users(Request $request)
    {
        $users = User::getAllUsers()->where('status', 1)->where('in_vacation', 0);

        if ($request->q) {
            $groups = Group::where('name', $request->q)
                ->orWhere('name', 'like', '%' . $request->q . '%')
                ->pluck('id')
                ->toArray();

            $users = $users->where(DB::raw('CONCAT(last_name, " ", first_name, " ", patronymic)'), 'like', '%' . $request->q . '%');

            if (!empty($groups)) {
                $users = $users->orWhereIn('group_id', [$groups]);
            }
        }

        if ($request->has('role')) {
            $role = $request->role;
            if ($role == 1 || $role == 2 || $role == 3) {
                $users->whereIn('users.department_id', [14]);
            } else if ($role == 4) {
                $usersFromGroup = $this->findAllUsersAndReturnGroupIds([53, 52, 54, 50, 74]);

                $users->whereIn('users.group_id',
                    array_unique(array_merge(['53'/*'16'*/, '52'/*'9'*/, '54', '50', '74'], $usersFromGroup))
                );
            } else if ($role == 5) {
                $usersFromGroup = $this->findAllUsersAndReturnGroupIds([8, 19, 13, 58]);

                if (in_array(Auth::user()->group_id, [13, 19])) {
                    $users->where('users.id', Auth::user()->id);
                }

                $users->whereIn('users.group_id',
                    array_unique(array_merge(['8'/*'5'*/, '19'/*'33'*/, '13', '58'], $usersFromGroup))
                );
            } else if ($role == 6) {
                $usersFromGroup = $this->findAllUsersAndReturnGroupIds([8, 27, 13]);

                if (in_array(Auth::user()->group_id, [13, 27])) {
                    $users->where('users.id', Auth::user()->id);
                }

                $users->whereIn('users.group_id',
                    array_unique(array_merge(['8'/*'5'*/, '27'/*'34'*/, '13'], $usersFromGroup))
                );
            } else if ($role == 7) {
                $usersFromGroup = $this->findAllUsersAndReturnGroupIds([54, 49]);

                $users->whereIn('users.group_id',
                    array_unique(array_merge([54/*26*/, 49/*32*/], $usersFromGroup))
                );
                // upper line changed by type of lines 135-138 ($RespRole7)
            } else if ($role == 8) {
                $usersFromGroup = $this->findAllUsersAndReturnGroupIds([23, 14]);

                $users->whereIn('users.group_id',
                    array_unique(array_merge([23, 14], $usersFromGroup))
                );
            } else if ($role == 9) {
                $usersFromGroup = $this->findAllUsersAndReturnGroupIds([14, 31]);

                $users->whereIn('users.group_id',
                    array_unique(array_merge([14, 31], $usersFromGroup))
                );
            }
        }

        $users = $users->get();

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                 'id' => $user->id,
                 'text' => $user->last_name . ' ' . $user->first_name . ' ' . $user->patronymic . ', Должность: ' . $user->group_name,
             ];
        }

        return ['results' => $results];
    }


    public function get_contractors(Request $request)
    {
        $contractors = Contractor::query();

        if ($request->q) {
            $contractors = $contractors->where('full_name', 'like', '%' . trim($request->q) . '%')
                ->orWhere('short_name', 'like', '%' . trim($request->q) . '%')
                ->orWhere('inn', 'like', '%' . trim($request->q) . '%')
                ->orWhere('kpp', 'like', '%' . trim($request->q) . '%');
        }


        if($request->contractor_id) {
            $contractors = $contractors->where('id', $request->contractor_id);
        }

        $contractors = $contractors->where('in_archive', 0)
            ->whereNotIn('id', $request->contractor_ids ? $request->contractor_ids : [])
            ->get();

        $results = [];
        foreach ($contractors->where('is_client', 1)->take(20) as $contractor) {
            $results[] = [
                 'id' => $contractor->id,
                 'text' => $contractor->short_name . ', ИНН: ' . $contractor->inn,
             ];
        }

        return ['results' => $results];
    }


    public function select_user(SelectResponsibleUserRequest $request, $id)
    {
        DB::beginTransaction();

        $project = Project::find($id);
        // check role existence
        $resp_user = ProjectResponsibleUser::where(['project_id' => $id, 'role' => $request->role]);
        $contract_resp = collect();
        if (in_array($request->role, ['7', '8', '9'])) {
            $contract_resp = ProjectResponsibleUser::where(['project_id' => $id, 'role' => $request->role])->get();
        }
        $resp_user = $resp_user->first();

        if ($resp_user) {
            $old_user = $resp_user->user_id;
        } else {
            $old_user = null;
        }

        if ($resp_user and !in_array($request->role, ['7', '8', '9'])) {
            // if exist, update
            $resp_user->user_id = $request->user;
            $resp_user->save();
        } elseif ($contract_resp->where('user_id', $request->user)->count() == 0) {
            // if !exist, create
            $resp_user = ProjectResponsibleUser::create([
                'project_id' => $id,
                'role' => $request->role,
                'user_id' => $request->user
            ]);
        }

        // If user exists and have role_id = 6 (meaning responsible for tongue),
        // then we have to add him as a responsible user in material accounting mode of object
        // Also, that function sets up, that accept has participation in material accounting
        if (in_array($request->role, ['6'])) {
            if ($old_user){
                $objectResponsibleUser = ObjectResponsibleUser::where('object_id', '=', $project->object_id)
                    ->where('user_id', '=', $old_user)
                    ->get()
                    ->first();
            } else {
                $objectResponsibleUser = ObjectResponsibleUser::where('object_id', '=', $project->object_id)
                    ->where('user_id', '=', $resp_user->user_id)
                    ->get()
                    ->first();
            }

            if ($objectResponsibleUser){
                $newObjectResponsibleUserExists = ObjectResponsibleUser::where('object_id', '=', $project->object_id)
                    ->where('user_id', '=', $request->user)
                    ->get()
                    ->first();

                if(!$newObjectResponsibleUserExists) {
                    $objectResponsibleUser->user_id = $request->user;
                    $objectResponsibleUser->save();
                }
            } else {
                $roleId = (new ObjectResponsibleUserRole)->getRoleIdBySlug('TONGUE_PROJECT_MANAGER');
                ObjectResponsibleUser::create([
                    'object_id' => $project->object_id,
                    'user_id' => $request->user,
                    'object_responsible_user_role_id' => $roleId
                ]);
            }

            $projectObject = ProjectObject::find($project->object_id);
            if ($projectObject) {
                $projectObject->is_participates_in_material_accounting = 1;
                $projectObject->save();
            }
        }

        if ($request->task24 or $request->task25) {
            $solved_task = Task::where('status', $request->task24 ? 24 : 25)->where('is_solved', 0)->first();
            $solved_task->final_note = $request->final_note;
            $solved_task->solve_n_notify();

            $respUser = User::find($request->user);
            $taskSolver = User::find($solved_task->responsible_user_id);

            /** Отправка уведомлений трём пользователям */
            $users = [$request->user, 6, 7];
            $name = "По проекту {$project->name} ({$project->object->address}) по направлению " .
                ($request->task24 ? " сваи " : "шпунт") . ", был выбран отв." .
                " РП - {$respUser->full_name}, автор назначения {$taskSolver->full_name}";
            $task_id = $solved_task->id;
            $contractor_id = $project->contractor_id;
            $project_id = $project->id;
            $object_id = $project->object_id;

            foreach ($users as $userId) {
                dispatchNotify(
                    $userId,
                    $name,
                    '',
                    NotificationType::RESPONSIBLE_SELECTED_FOR_PROJECT_DIRECTION_PROJECT_LEADER,
                    [
                        'task_id' => $task_id,
                        'contractor_id' => $contractor_id,
                        'project_id' => $project_id,
                        'object_id' => $object_id
                    ]
                );
            }
        }

        if (!empty($resp_user->getChanges())) {
            // if we update resp_user
            $user_roles = new User();

            $tasks = Task::where(['responsible_user_id' => $old_user, 'is_solved' => 0, 'project_id' => $id])->whereIn('status', $user_roles->role_tasks[$request->role])->get();
            // move all tasks from old user to new user
            foreach ($tasks as $updated_task) {
                $project = Project::find($updated_task->project_id);
                $new_user = User::find($resp_user->user_id);

                if ($updated_task->responsible_user_id != 6) {
                    // notify old user
                    dispatchNotify(
                        $updated_task->responsible_user_id,
                        'Задача «' . $updated_task->name . '» передана пользователю ' . $new_user->long_full_name,
                        '',
                        NotificationType::TASK_TRANSFER_NOTIFICATION_TO_NEW_RESPONSIBLE,
                        [
                            'task_id' => $updated_task->id,
                            'contractor_id' => $updated_task->project_id ? $project->contractor_id : null,
                            'project_id' => $updated_task->project_id ? $updated_task->project_id : null,
                            'object_id' => $updated_task->project_id ? $project->object_id : null,
                        ]
                    );

                    // notify new user
                    dispatchNotify(
                        $resp_user->user_id,
                        'Новая задача «' . $updated_task->name . '»',
                        '',
                        NotificationType::STANDARD_TASK_CREATION_NOTIFICATION,
                        [
                            'additional_info' => ' Ссылка на задачу: ' . $updated_task->task_route(),
                            'task_id' => $updated_task->id,
                            'contractor_id' => $updated_task->project_id ? $project->contractor_id : null,
                            'project_id' => $updated_task->project_id ? $updated_task->project_id : null,
                            'object_id' => $updated_task->project_id ? $project->object_id : null,
                        ]
                    );
                }

                // move task to new user
                $updated_task->update(['responsible_user_id' => $resp_user->user_id]);
            }
        } elseif ($resp_user->wasRecentlyCreated) {
            // if we create new resp_user, we need to check tasks existence
            if ($request->role == 2) {
                $task = Task::where('project_id', $id)->where('status', 15)->where('is_solved', 0)->first();

                if ($task) {
                    $project = Project::find($id);
                    $work_volumes = WorkVolume::where('project_id', $id)->where('type', 0)->whereStatus(2)->get();
                    $offers_count = CommercialOffer::where('project_id', $id)->where('is_tongue', 1)->update(['status' => 3]);

                    $task->final_note = $task->descriptions[$task->status] . User::find($request->user)->full_name;
                    $task->solve_n_notify();

                    foreach ($work_volumes as $work_volume) {
                        $prev_com_offer = CommercialOffer::where('project_id', $project->id)->where('is_tongue', 1)->where('work_volume_id', $work_volume->id)->orderBy('version', 'desc')->first();

                        $commercial_offer = new CommercialOffer();

                        $commercial_offer->name = 'Коммерческое предложение (шпунтовое направление)';
                        $commercial_offer->user_id = Auth::id();
                        $commercial_offer->project_id = $work_volume->project_id;
                        $commercial_offer->work_volume_id = $work_volume->id;
                        $commercial_offer->option = $work_volume->option;
                        $commercial_offer->status = 1;
                        $commercial_offer->version = $offers_count + 1;
                        $commercial_offer->file_name = 0;
                        $commercial_offer->is_tongue = 1;

                        $commercial_offer->save();

                        if ($prev_com_offer) {
                            foreach ($prev_com_offer->notes as $item) {
                                $new_note = $item->replicate();
                                $new_note->commercial_offer_id = $commercial_offer->id;
                                $new_note->save();
                            }

                            foreach ($prev_com_offer->requirements as $item) {
                                $new_note = $item->replicate();
                                $new_note->commercial_offer_id = $commercial_offer->id;
                                $new_note->save();
                            }

                            foreach ($prev_com_offer->advancements as $item) {
                                $new_note = $item->replicate();
                                $new_note->commercial_offer_id = $commercial_offer->id;
                                $new_note->save();
                            }

                            //take new materials
                            $new_wv_mats = WorkVolumeMaterial::where('work_volume_id', $work_volume->id)->get();
                            $split_adapter = $new_wv_mats->groupBy('manual_material_id')->map(function ($group) { return $group->sum('count');});

                            //get splits from previous com_offer
                            $control_count = $prev_com_offer->mat_splits->groupBy('man_mat_id'); //here are old ones

                            //creating splits for new commercial_offer
                            foreach ($split_adapter as $manual_id => $count) {
                                if ($count == (isset($control_count[$manual_id]) ? $control_count[$manual_id]->sum('count') : -1 )) { //if there was no changes amount of
                                    foreach ($control_count[$manual_id] as $old_split) {
                                        $new_split = $old_split->replicate();
                                        $new_split->man_mat_id = $old_split->man_mat_id;
                                        $new_split->com_offer_id = $commercial_offer->id;
                                        $new_split->save();
                                    }
                                } else {
                                    CommercialOfferMaterialSplit::create([
                                        'man_mat_id' => $manual_id,
                                        'count' => $count,
                                        'type' => 1,
                                        'com_offer_id' => $commercial_offer->id,
                                    ]);
                                }
                            }

                        } else {
                            foreach ($work_volume->materials->groupBy('manual_material_id') as $man_mat_id => $wv_mats) {
                                CommercialOfferMaterialSplit::create([
                                    'man_mat_id' => $man_mat_id,
                                    'type' => 1,
                                    'count' => $wv_mats->sum('count'),
                                    'com_offer_id' => $commercial_offer->id,
                                ]);
                            }
                        }

                        $task = new Task([
                            'project_id' => $work_volume->project_id,
                            'name' => 'Формирование КП (шпунтовое направление)',
                            'responsible_user_id' => ProjectResponsibleUser::where('project_id', $work_volume->project_id)->where('role', 2)->first()->user_id,
                            'contractor_id' => $project->contractor_id,
                            'target_id' => $commercial_offer->id,
                            'prev_task_id' => Task::where('target_id', $work_volume->id)->where('status', 18)->where('is_solved', 1)->first()->id,
                            'expired_at' => Carbon::now()->addHours(24),
                            'status' => 5
                        ]);

                        $task->save();

                        dispatchNotify(
                            $task->responsible_user_id,
                            'Новая задача «' . $task->name . '»',
                            '',
                            NotificationType::OFFER_CREATION_SHEET_PILING_TASK_NOTIFICATION,
                            [
                                'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                                'task_id' => $task->id,
                                'contractor_id' => $task->project_id ? Project::find($task->project_id)->contractor_id : null,
                                'project_id' => $task->project_id ? $task->project_id : null,
                                'object_id' => $task->project_id ? Project::find($task->project_id)->object_id : null,
                            ]
                        );

                        $com_offer_request = new CommercialOfferRequest();
                        $com_offer_request->user_id = 0;
                        $com_offer_request->project_id = $work_volume->project_id;
                        $com_offer_request->commercial_offer_id = $commercial_offer->id;
                        $com_offer_request->status = 0;
                        $com_offer_request->description = 'Сформирован новый объём работ, проверьте актуальность цен';
                        $com_offer_request->is_tongue = $commercial_offer->is_tongue;

                        $com_offer_request->save();
                    }
                }
            } else if ($request->role == 4) {
                $task = Task::where('project_id', $id)->where('status', 14)->where('is_solved', 0)->first();

                if ($task) {
                    $task->final_note = $task->descriptions[$task->status] . User::find($request->user)->full_name;
                    $task->solve_n_notify();

                    $work_volumes = WorkVolume::whereProjectId($id)->whereType(0)->whereStatus(1)->get();
                    foreach ($work_volumes as $work_volume) {
                        $tongueTask = new Task();
                        $tongueTask->project_id = $id;
                        $tongueTask->name =  'Расчёт объемов (шпунтовое направление)';
                        $tongueTask->status = 3;
                        $tongueTask->responsible_user_id = $resp_user->user_id;
                        $tongueTask->contractor_id = Project::find($id)->contractor_id;
                        $tongueTask->expired_at = Carbon::now()->addHours(24);
                        $tongueTask->target_id = $work_volume->id;
                        $tongueTask->save();
                    }

                    dispatchNotify(
                        $tongueTask->responsible_user_id,
                        'Новая задача «' . $tongueTask->name . '»',
                        '',
                        NotificationType::SHEET_PILING_CALCULATION_TASK_CREATION_NOTIFICATION,
                        [
                            'additional_info' => ' Ссылка на задачу: ' . $task->task_route(),
                            'task_id' => $tongueTask->id,
                            'contractor_id' => $tongueTask->project_id ? Project::find($tongueTask->project_id)->contractor_id : null,
                            'project_id' => $tongueTask->project_id ? $tongueTask->project_id : null,
                            'object_id' => $tongueTask->project_id ? Project::find($tongueTask->project_id)->object_id : null,
                        ]
                    );
                }
            }
        }

        DB::commit();

        $redirect_path = strpos(url()->previous(), 'tasks') ? 1 : 0;

        if ($redirect_path) {
            return redirect()->route('tasks::index');
        } else {
            return redirect()->back()->with('users', 'Ответственный добавлен');
        }
    }


    public function delete_resp_user(Request $request)
    {
        $project = Project::findOrFail($request->project_id);

        $this->authorize('edit', $project);

        $user_tasks_count = Task::where('responsible_user_id', $request->user_id)->where('project_id', $request->project_id)->where('is_solved', 0)->count();

        if (!$user_tasks_count) {
            DB::beginTransaction();

            ProjectResponsibleUser::where('user_id', $request->user_id)->where('project_id', $request->project_id)->where('role', $request->role)->delete();

            Session::flash('users', 'Ответственный удален');

            DB::commit();
        }

        $objectResponsibleUser = ObjectResponsibleUser::where('object_id', '=' , $project->object_id)
            ->where('user_id', '=', $request->user_id)
            ->get()
            ->first();

        if ($objectResponsibleUser) {
            $objectResponsibleUser->delete();
        }

        return response()->json(!$user_tasks_count ? true : false);
    }


    public function edit($id)
    {
        $project = Project::findOrFail($id);

        $this->authorize('edit', $project);

        return view('projects.edit', [
            'project' => $project,
            'object' => ProjectObject::findOrFail($project->object_id)
        ]);
    }


    public function add_contact(ContractorContactRequest $request, $id)
    {
        DB::beginTransaction();

        $project = Project::findOrFail($request->project_id);

        $this->authorize('edit', $project);

        $contact = new ContractorContact();

        $contact->first_name = $request->first_name;
        $contact->last_name = $request->last_name;
        $contact->patronymic = $request->patronymic;
        $contact->position = $request->position;
        $contact->email = $request->email;
        $contact->note = $request->note;
        $contact->contractor_id = $id;

        $contact->save();

        foreach ($request->phone_count as $phone => $main_id) {
            if (trim(preg_replace('~[\D]~', '', $request->phone_number[$phone])) != '') {
                ContractorContactPhone::create([
                    'name' => $request->phone_name[$phone],
                    'phone_number' => preg_replace('~[\D]~', '', $request->phone_number[$phone]),
                    'dop_phone' => $request->phone_dop[$phone],
                    'type' => 1,
                    'is_main' => $request->main == $main_id,
                    'contact_id' => $contact->id
                ]);
            }
        }

        $p_contact = new ProjectContact();

        $p_contact->contact_id = $contact->id;
        $p_contact->project_id = $request->project_id;
        $p_contact->note = $request->project_note;

        $p_contact->save();

        DB::commit();

        return redirect()->back()->with('contacts', 'Новый контакт добавлен');
    }


    public function update(ProjectRequest $request, $id)
    {
        DB::beginTransaction();

        $project = Project::findOrFail($id);

        $this->authorize('edit', $project);

        $project->is_tongue = in_array('is_tongue', $request->type);
        $project->is_pile = in_array('is_pile', $request->type);
        $project->name = $request->name;
        $project->object_id = $request->object_id;
        $project->entity = $request->entity;
        $project->description = $request->description;

        $project->save();

        DB::commit();

        return redirect()->route('projects::card', $id);
    }


    public function contact_delete(Request $request)
    {
        DB::beginTransaction();

        $project = Project::findOrFail($request->project_id);

        $this->authorize('edit', $project);

        $contact = ProjectContact::where('contact_id', $request->contact_id)->where('project_id', $request->project_id);

        $contact->delete();

        DB::commit();

        Session::flash('contacts', "Контакт удален из проекта");

        return \GuzzleHttp\json_encode(true);
    }


    public function tasks(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $com_offers = CommercialOffer::where('project_id', $id);
        $work_volumes = WorkVolume::where('work_volumes.project_id', $id)
            ->orderBy('work_volumes.version', 'desc')
            ->with('get_requests');;
        $contractor = Contractor::findOrFail($project->contractor_id);
        $contacts = ContractorContact::where('contractor_id', $contractor->id)->get();

        $solved_tasks = Task::orderBy('created_at', 'desc')
            ->with('responsible_user', 'author', 'user')
            ->where('tasks.project_id', $id)
            ->leftJoin('users', 'users.id', '=', 'tasks.responsible_user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->leftjoin('project_objects', 'project_objects.id', 'projects.object_id')
            ->select('users.last_name', 'users.first_name', 'users.patronymic', 'projects.name as project_name',
                'contractors.short_name as contractor_name', 'project_objects.address as object_address', 'tasks.*');

        if ($request->search) {
            $solved_tasks = $solved_tasks->where('full_name', 'like', '%' . $request->search . '%')
                ->orWhere('tasks.name', 'like', '%' . $request->search . '%')
                ->orWhere('contractors.short_name', 'like', '%' . $request->search . '%');
        }

        $task_files = TaskFile::whereIn('task_files.task_id', $solved_tasks->pluck('tasks.id'))
            ->leftJoin('users', 'users.id', '=', 'task_files.user_id')
            ->select('users.last_name', 'users.first_name', 'users.patronymic', 'task_files.*');

        $task_redirects = TaskRedirect::select('task_redirects.*', 'tasks.project_id')
            ->leftJoin('tasks', 'tasks.id', 'task_redirects.task_id')
            ->where('tasks.project_id', $id);

        $task_responsible_users = User::select('users.id', 'users.first_name', 'users.last_name', 'users.patronymic')
            ->whereIn('users.id', $solved_tasks->pluck('responsible_user_id'))
            ->orWhereIn('users.id', $task_redirects->pluck('old_user_id'))
            ->orWhereIn('users.id', $task_redirects->pluck('responsible_user_id'))
            ->orWhereIn('users.id', $solved_tasks->pluck('description'));

        $com_offers_task = CommercialOffer::whereIn('project_id', $solved_tasks->pluck('project_id'))->get();

        $solved_tasks = $solved_tasks->get();

        return view('projects.tasks', [
            'solved_tasks' => $solved_tasks,
            'task_files' => $task_files->get(),
            'task_responsible_users' => $task_responsible_users->get(),
            'task_redirects' => $task_redirects->get(),
            'project' => $project,
            'contractor' => $contractor,
            'com_offers_task' => $com_offers_task,
            'contacts' => $contacts,
            'work_volumes' => $work_volumes->get(),
            'com_offers' => $com_offers->get(),
        ]);
    }


    public function get_project_documents(Request $request, $project_id)
    {
        $documents = ProjectDocument::where('project_id', $project_id);

        $project = Project::findOrFail($project_id);

        $this->authorize('edit', $project);

        if ($request->q) {
            $documents = $documents->where('name', 'like', '%' . trim($request->q) . '%');
        }

        $documents = $documents->get();

        $results = [];
        foreach ($documents as $documents) {
            $results[] = [
                 'id' => $documents->id,
                 'text' => $documents->name,
             ];
        }

        return ['results' => $results];
    }

    public function use_as_main(Request $request)
    {
        DB::beginTransaction();
        // find relation
        $relation = ProjectContractors::findOrFail($request->relation_id);

        $project = Project::findOrFail($relation->project_id);

        $this->authorize('edit', $project);
        // change places
        $relation->useAsMain();

        DB::commit();

        return response()->json(true);
    }

    public function remove_relation(Request $request)
    {
        DB::beginTransaction();

        // find relation, remove it
        $relation = ProjectContractors::findOrFail($request->relation_id);

        ProjectContractorsChangeHistory::create([
            'old_contractor_id' => $relation->contractor_id,
            'project_id' => $relation->project_id,
            'user_id' => Auth::id(),
        ]);

        $relation->delete();

        DB::commit();

        return response()->json(true);
    }

    public function add_contractors(Request $request, $project_id)
    {
        DB::beginTransaction();

        $project = Project::findOrFail($project_id);

        $this->authorize('edit', $project);

        foreach ($request->contractor_ids as $contractor_id) {
            if ($contractor_id != 'default') {
                ProjectContractors::create([
                    'project_id' => $project_id,
                    'contractor_id' => $contractor_id,
                    'user_id' => Auth::id()
                ]);

                ProjectContractorsChangeHistory::create([
                    'new_contractor_id' => $contractor_id,
                    'project_id' => $project_id,
                    'user_id' => Auth::id(),
                ]);
            }
        }

        DB::commit();

        return back()->with('add_contractors', 'Контрагенты добавлены');
    }

    public function render_contact($contact_id, $key)
    {
        // selected person
        $contact = ContractorContact::with('phones')->where('contractor_contacts.id', $contact_id)
            ->leftJoin('project_contacts', function($leftJoin) use ($contact_id)
            {
                $leftJoin->on('contractor_contacts.id', '=', 'project_contacts.contact_id');
                $leftJoin->on(DB::raw('project_contacts.project_id'), DB::raw('='),DB::raw("'".$contact_id."'"));
            })
            ->select('contractor_contacts.*', 'project_contacts.id as proj_contact_id', 'project_contacts.note as proj_contact_note')
            ->first();

        foreach ($contact->phones as $phone) {
            preg_match("/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{0,2})$/", $phone->phone_number, $matches);
            if(count($matches) > 2) {
                $phone->phone_number = '+' . $matches[1] . ' (' . $matches[2] . ') ' . implode(array_slice(array_filter($matches), 3, 3), '-');
            }
        }

        $html = view('projects.contact-row', ['contact' => $contact, 'key' => $key])->render();

        return $html;
    }

    public function store_temp_contact(Request $request)
    {
        if (count($request->all()) > 4) {
            // manually added person
            DB::beginTransaction();

            $contact = new ContractorContact();

            $contact->first_name = $request->first_name;
            $contact->last_name = $request->last_name;
            $contact->patronymic = $request->patronymic;
            $contact->position = $request->position;
            $contact->email = $request->email;
            $contact->note = $request->note;
            $contact->contractor_id = 0;

            $contact->save();

            foreach ($request->phone_count as $phone => $main_id) {
                if (trim(preg_replace('~[\D]~', '', $request->phone_number[$phone])) != '') {
                    ContractorContactPhone::create([
                        'name' => $request->phone_name[$phone],
                        'phone_number' => preg_replace('~[\D]~', '', $request->phone_number[$phone]),
                        'dop_phone' => $request->phone_dop[$phone],
                        'type' => 1,
                        'is_main' => $request->main == $main_id,
                        'contact_id' => $contact->id
                    ]);
                }
            }

            $p_contact = new ProjectContact();

            $p_contact->contact_id = $contact->id;
            $p_contact->project_id = 0;
            $p_contact->note = $request->project_note;

            $p_contact->save();

            DB::commit();

            return response()->json([
                ['contractor_contact_id' => $contact->id,
                'project_contact_id' => $p_contact->id],
                'html' => $this->render_contact($contact->id, $request->key)
            ]);
        }


        // selected person
        DB::beginTransaction();

        $p_contact = ProjectContact::firstOrCreate(
            ['contact_id' => $request->contact_id, 'project_id' => 0]
        );
        $p_contact->note = $request->project_note;
        $p_contact->save();

        DB::commit();

        return response()->json([
            ['contractor_contact_id' => $request->contact_id,
                'project_contact_id' => $p_contact->id],
            'html' => $this->render_contact($request->contact_id, $request->key)
        ]);
    }

    public function updateContacts(iterable $contractor_contact_ids = [], iterable $project_contacts_ids = [], int $contractor_id, int $project_id)
    {
        DB::beginTransaction();

        ContractorContact::whereIn('id', $contractor_contact_ids)->update(['contractor_id' => $contractor_id]);
        ProjectContact::whereIn('id', $project_contacts_ids)->update(['project_id' => $project_id]);

        DB::commit();

        return response()->json([
            'url' => route('projects::card', $project_id)
        ]);
    }

    public function close_project(Request $request, $project_id)
    {
        DB::beginTransaction();

        $project = Project::findOrFail($project_id);

        foreach ($project->com_offers as $com_offer) {
            if ($com_offer->contracts()->whereIn('status', [5, 6])->first()) {
                return redirect()->back();
            }
        }

        $work_volumes = $project->wvs->whereIn('id', $request->wv_ids);
        $work_volumes->each->decline();

        $decline = collect();
        foreach ($work_volumes as $wv) {
            $decline->push(['option' => $wv->option, 'type' => $wv->type]);
        }
        // dd($decline);
        foreach ($decline as $item) {
            $is_tongue = ($item['type'] == 0 ? '1' : '0');
            $project->com_offers->where('is_tongue', $is_tongue)->where('option', $item['option'])->each->decline();
            $project->com_offers->where('is_tongue', $is_tongue)->where('option', $item['option'])->each->contracts->each->decline();
        }

        DB::commit();

        return redirect()->back();
    }

    public function get_project_options()
    {
        $project = Project::findOrFail(request()->project_id);
        $types = [0, 1];
        if (request()->type) {
            $types = [(request()->type ? 0 : 1)];
        }
        $options = $project->work_volumes->whereIn('type', $types)->pluck('option', 'id')->unique();
        $results[] = [
            'id' => 'disabled',
            'text' => 'Нажмите Enter, чтобы выбрать свой вариант',
            'disabled' => true,
        ];
        foreach ($options as $id => $option) {
            $results[] = [
                'id' => trim($option) ?: ('id:' . $id),
                'text' => trim($option) ?: ('id:' . $id),
            ];
        }

        return \GuzzleHttp\json_encode(['results' => $results]);
    }

    public function importance_toggler()
    {
        DB::beginTransaction();

        $project = Project::findOrFail(request('project_id'));
        $project->importanceToggler();

        DB::commit();

        return response()->json(true);
    }

    public function getProjects(Request $request)
    {
        $projects = Project::query();

        if ($request->q) {
            $projects->where('name', 'like', '%' . $request->q . '%');
        }

        return $projects->limit(20)->get()->map(function ($project) {
            return ['code' => $project->id . '', 'label' => $project->name];
        });
    }

    public function updateTimeResponsibleUser(ProjectTimeResponsibleUserRequest $request)
    {
        DB::beginTransaction();

        if ($request->task_id) {
            Task::find($request->task_id)->solve_n_notify();
        }
        $project = Project::findOrFail($request->project_id);
        $project->update(['time_responsible_user_id' => $request->time_responsible_user_id]);

        DB::commit();

        if ($request->task_id) {
            return redirect()->route('tasks::index');
        }
        return response()->json(true);
    }

    /**
     * Function return projects for given filter with special
     * label that contains project name ond object name_tag property.
     * Filter by project name and object address or name
     * @param Request $request
     * @return array
     */
    public function getProjectsForHumanAccounting(Request $request): array
    {
        $projects = Project::query();

        if ($request->q) {
            $projects = $projects->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->q}%")
                    ->orWhere('id', $request->q)
                    ->orWhereHas('object', function ($object) use ($request) {
                        $object->where('short_name', 'like', "%{$request->q}%")
                            ->orWhere('name', 'like', "%{$request->q}%")
                            ->orWhere('address', 'like', "%{$request->q}%");
                    });
            });
        }
        if ($request->daily === true) {
            $projects->where('time_responsible_user_id', auth()->id());
        }

        $projects = $projects->take(10)->get();

        if ($request->has('selected')) {
            // If we need to get some special project
            $project = Project::find($request->get('selected'));
            if ($project) {
                // Add it to the beginning
                $projects->prepend($project);
            }
        }

        return $projects->map(function ($project) {
            return ['code' => $project->id . '', 'label' => $project->id . ') ' .$project->name_with_object];
        })->toArray();
    }
}
