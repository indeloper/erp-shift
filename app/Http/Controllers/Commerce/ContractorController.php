<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContractorRequests\ContractorContactRequest;
use App\Http\Requests\ContractorRequests\ContractorStoreRequest;
use App\Http\Requests\ContractorRequests\ContractorUpdateRequest;
use App\Models\CommercialOffer\CommercialOffer;
use App\Models\Contract\Contract;
use App\Models\Contractors\BankDetail;
use App\Models\Contractors\Contractor;
use App\Models\Contractors\ContractorContact;
use App\Models\Contractors\ContractorContactPhone;
use App\Models\Contractors\ContractorPhone;
use App\Models\Contractors\ContractorType;
use App\Models\Group;
use App\Models\Project;
use App\Models\ProjectContact;
use App\Models\Task;
use App\Models\TaskFile;
use App\Models\TaskRedirect;
use App\Models\User;
use App\Models\WorkVolume\WorkVolume;
use App\Notifications\Contractor\ContractorDeletionControlTaskNotice;
use App\Notifications\Contractor\ContractorDeletionControlTaskResolutionNotice;
use App\Traits\TimeCalculator;
use Fomvasss\Dadata\Facades\DadataSuggest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ContractorController extends Controller
{
    use TimeCalculator;

    public function index(Request $request): View
    {
        $contractors = Contractor::query()->orderBy('id', 'desc');

        if ($request->search) {
            $contractors->getModel()->smartSearch($contractors,
                [
                    'full_name',
                    'short_name',
                    'inn',
                    'kpp',
                    'ogrn',
                    'legal_address',
                ],
                $request->search);
        }

        $contractors->where('in_archive', 0);

        return view('contractors.index', [
            'contractors' => $contractors->paginate(20),
        ]);
    }

    public function create(): View
    {
        // $contractorTypes = Contractor::CONTRACTOR_TYPES;
        // $contractorTypes = ContractorType::pluck('name')->toArray();

        $contractorTypes = [];
        foreach (ContractorType::all() as $contractorType) {
            $contractorTypes[$contractorType->id] = $contractorType->name;
        }

        return view('contractors.create', compact('contractorTypes'));
    }

    public function store(ContractorStoreRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        $contractor = new Contractor();

        $contractor->full_name = $request->full_name;
        $contractor->short_name = $request->short_name;
        $contractor->inn = $request->inn;
        $contractor->kpp = $request->kpp;
        $contractor->ogrn = $request->ogrn;
        $contractor->legal_address = $request->legal_address;
        $contractor->physical_adress = $request->physical_adress;
        $contractor->general_manager = $request->general_manager;
        $contractor->email = $request->email;
        $contractor->user_id = Auth::id();
        $types = $request->types;
        $contractor->main_type = array_shift($types);

        $contractor->save();

        foreach ($types as $additional_type) {
            $contractor->additional_types()->create([
                'additional_type' => $additional_type,
                'user_id' => auth()->id(),
            ]);
        }

        foreach ($request->phone_count as $phone => $main_id) {
            if (trim(preg_replace('~[\D]~', '', $request->phone_number[$phone])) != '') {
                ContractorPhone::create([
                    'name' => isset($request->phone_name[$phone]) ? $request->phone_name[$phone] : '',
                    'phone_number' => preg_replace('~[\D]~', '', $request->phone_number[$phone]),
                    'dop_phone' => $request->phone_dop[$phone],
                    'type' => 1,
                    'is_main' => $request->main == $main_id,
                    'contractor_id' => $contractor->id,
                ]);
            }
        }

        $bank = new BankDetail();

        $bank->bank_name = $request->bank_name;
        $bank->check_account = $request->check_account;
        $bank->cor_account = $request->cor_account;
        $bank->bik = $request->bik;
        $bank->contractor_id = $contractor->id;

        $bank->save();

        DB::commit();

        if ($request->task_id) {
            return redirect()->route('tasks::new_call', [$request->task_id, 'contractor_id' => $contractor->id]);
        }

        return redirect()->route('contractors::card', $contractor->id);
    }

    public function search_dadata(Request $request)
    {
        $type = $request->type ?? 'party';

        try {
            $result = DadataSuggest::suggest($type, ['query' => $request->search]);
        } catch (\RuntimeException $e) {
            $result['suggestions'] = [];
        }
        //this if is for different verions of datata api (mine is 2.1.1 on server we have 2.2.2)
        if (! isset($result['suggestions']) and count($result) > 0) {
            $save_plave = $result;
            $result = [];
            $result['suggestions'] = $save_plave;
        }

        if ($request->is_edit) {
            return view('contractors.edit', [
                'type' => $type,
                'result' => ! empty($result['suggestions']) ? ($result['suggestions'][isset($request->id) ? $request->id : 0]) : '',
                'results' => $result['suggestions'],
                'old_search' => $request->search ? $request->search : '',
                'contractor' => Contractor::findOrFail($request->contractor_id),
                'bank' => BankDetail::where('contractor_id', $request->contractor_id)->first(),
            ]);
        }

        if ($request->ajax) {
            return \GuzzleHttp\json_encode($result['suggestions']);

        } else {
            return view('contractors.create', [
                'type' => $type,
                'result' => ! empty($result['suggestions']) ? ($result['suggestions'][isset($request->id) ? $request->id : 0]) : '',
                'results' => $result['suggestions'],
                'old_search' => $request->search ? $request->search : '',
            ]);
        }
    }

    public function card(Request $request, $id): View
    {
        $projects = Project::getAllProjects()->with(['contracts' => function ($q) {
            $q->withCount('get_requests');
        }])->where('projects.contractor_id', $id);
        $com_offers = CommercialOffer::whereIn('project_id', $projects->pluck('id'))
            ->leftjoin('users', 'users.id', '=', 'commercial_offers.user_id')
            ->select('commercial_offers.*', DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) as user_full_name'))
            ->get();

        $solved_tasks = Task::orderBy('created_at', 'desc')
            ->with('responsible_user', 'author', 'user')
            ->where('tasks.contractor_id', $id)
            ->leftJoin('users', 'users.id', '=', 'tasks.user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->leftjoin('project_objects', 'project_objects.id', 'projects.object_id')
            ->select(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) AS full_name'),
                'projects.name as project_name', 'project_objects.address as object_address',
                'contractors.short_name as contractor_name', 'tasks.*');

        $task_files = TaskFile::whereIn('task_files.task_id', $solved_tasks->pluck('tasks.id'))
            ->leftJoin('users', 'users.id', '=', 'task_files.user_id')
            ->select(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) AS full_name'), 'task_files.*');

        $task_redirects = TaskRedirect::select('task_redirects.*', 'tasks.project_id')
            ->leftJoin('tasks', 'tasks.id', 'task_redirects.task_id')
            ->where('tasks.project_id', $id);

        $task_responsible_users = User::select('users.id', 'users.first_name', 'users.last_name')
            ->whereIn('users.id', $solved_tasks->pluck('responsible_user_id'))
            ->orWhereIn('users.id', $task_redirects->pluck('old_user_id'))
            ->orWhereIn('users.id', $task_redirects->pluck('responsible_user_id'))
            ->orWhereIn('users.id', $solved_tasks->pluck('description'));

        $work_volumes = WorkVolume::where('project_id', $id)
            ->orderBy('work_volumes.version', 'desc')
            ->with('get_requests');

        $com_offers_task = CommercialOffer::whereIn('project_id', $solved_tasks->pluck('project_id'))->get();

        $solved_tasks = $solved_tasks->take(5)->get();

        foreach ($solved_tasks as $solved_task) {
            $com_for_tasks = CommercialOffer::where('project_id', $solved_task->project_id)->whereIn('commercial_offers.status', [1, 2, 4, 5])->first();
            $wv_for_tasks = WorkVolume::where('work_volumes.project_id', $solved_task->project_id)->whereIn('work_volumes.status', [1, 2])->first();

            $solved_task->commercial_offer_id = is_null($com_for_tasks) ? '' : $com_for_tasks->id;
            $solved_task->work_volume_id = is_null($wv_for_tasks) ? '' : $wv_for_tasks->id;
            $solved_task->commercial_offer_file = is_null($com_for_tasks) ? '' : $com_for_tasks->file_name;
        }

        $contracts = Contract::where('subcontractor_id', $id)->get();

        $contractor = Contractor::with('phones')->findOrFail($id);

        if ($contractor->in_archive) {
            abort(403);
        }

        foreach ($contractor->phones as $phone) {
            preg_match("/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{0,2})$/", $phone->phone_number, $matches);
            if (count($matches) > 2) {
                $phone->phone_number = '+'.$matches[1].' ('.$matches[2].') '.implode(array_slice(array_filter($matches), 3, 3), '-');
            }
        }

        $contacts = ContractorContact::with('phones')->where('contractor_id', $id)->get();
        foreach ($contacts as $contact) {
            foreach ($contact->phones as $phone) {
                preg_match("/^(\d{1})(\d{3})(\d{3})(\d{2})(\d{0,2})$/", $phone->phone_number, $matches);
                if (count($matches) > 2) {
                    $phone->phone_number = '+'.$matches[1].' ('.$matches[2].') '.implode(array_slice(array_filter($matches), 3, 3), '-');
                }
            }
        }

        $projects_com_offer = Project::whereIn('id', $contracts->pluck('project_id')->toArray())->get();

        return view('contractors.card', [
            'contractor' => $contractor,
            'bank' => BankDetail::where('contractor_id', $id)->first(),
            'contacts' => $contacts,
            'projects' => $projects->get(),
            'com_offers' => $com_offers,
            'solved_tasks' => $solved_tasks,
            'task_files' => $task_files->get(),
            'task_responsible_users' => $task_responsible_users->get(),
            'task_redirects' => $task_redirects->get(),
            'com_offers_task' => $com_offers_task,
            'work_volumes' => $work_volumes->get(),
            'projects_com_offer' => $projects_com_offer,
            'contracts' => $contracts,
        ]);
    }

    public function edit($id): View
    {
        $contractor = Contractor::findOrFail($id);
        // $contractorTypes = Contractor::CONTRACTOR_TYPES;
        $contractorTypes = [];
        foreach (ContractorType::all() as $contractorType) {
            $contractorTypes[$contractorType->id] = $contractorType->name;
        }

        if ($contractor->in_archive) {
            abort(403);
        }

        return view('contractors.edit', [
            'contractor' => $contractor,
            'bank' => BankDetail::where('contractor_id', $id)->first(),
            'contractorTypes' => $contractorTypes,
        ]);
    }

    public function update(ContractorUpdateRequest $request, $id): RedirectResponse
    {
        DB::beginTransaction();

        $contractor = Contractor::findOrFail($id);

        $contractor->full_name = $request->full_name;
        $contractor->short_name = $request->short_name;
        $contractor->inn = $request->inn;
        $contractor->kpp = $request->kpp;
        $contractor->ogrn = $request->ogrn;
        $contractor->legal_address = $request->legal_address;
        $contractor->physical_adress = $request->physical_adress;
        $contractor->general_manager = $request->general_manager;
        $contractor->email = $request->email;
        $types = $request->types;
        $contractor->main_type = array_shift($types);
        $contractor->save();
        $contractor->phones()->delete();
        $contractor->additional_types()->delete();

        foreach ($types as $additional_type) {
            $contractor->additional_types()->create([
                'additional_type' => $additional_type,
                'user_id' => auth()->id(),
            ]);
        }

        foreach ($request->phone_count as $phone => $main_id) {
            if (trim(preg_replace('~[\D]~', '', $request->phone_number[$phone])) != '') {
                ContractorPhone::create([
                    'name' => $request->phone_name[$phone],
                    'phone_number' => preg_replace('~[\D]~', '', $request->phone_number[$phone]),
                    'dop_phone' => $request->phone_dop[$phone],
                    'type' => 1,
                    'is_main' => $request->main == $main_id,
                    'contractor_id' => $contractor->id,
                ]);
            }
        }

        $bank = BankDetail::where('contractor_id', $id)->firstOrNew(['contractor_id' => $id]);

        $bank->bank_name = $request->bank_name;
        $bank->check_account = $request->check_account;
        $bank->cor_account = $request->cor_account;
        $bank->bik = $request->bik;
        $bank->contractor_id = $id;

        $bank->save();

        DB::commit();

        return redirect()->route('contractors::card', $id);
    }

    public function add_contact(ContractorContactRequest $request, $id): RedirectResponse
    {
        DB::beginTransaction();

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
                    'contact_id' => $contact->id,
                ]);
            }
        }

        if ($request->task_id) {
            return redirect()->route('tasks::new_call', [$request->task_id, 'contractor_id' => $id, 'project_id' => $request->project_id, 'contact_id' => $contact->id]);
        }

        DB::commit();

        return redirect()->back()->with('contacts', 'Новый контакт добавлен');
    }

    public function edit_contact(ContractorContactRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        $contact = ContractorContact::findOrFail($request->id);

        $contact->first_name = $request->first_name;
        $contact->last_name = $request->last_name;
        $contact->patronymic = $request->patronymic;
        $contact->position = $request->position;
        $contact->email = $request->email;
        $contact->note = $request->note;

        $contact->save();
        $contact->phones()->delete();

        foreach ($request->phone_count as $phone => $main_id) {
            if (trim(preg_replace('~[\D]~', '', $request->phone_number[$phone])) != '') {
                ContractorContactPhone::create([
                    'name' => $request->phone_name[$phone],
                    'phone_number' => preg_replace('~[\D]~', '', $request->phone_number[$phone]),
                    'dop_phone' => $request->phone_dop[$phone],
                    'type' => 1,
                    'is_main' => $request->main == $main_id,
                    'contact_id' => $contact->id,
                ]);
            }
        }

        if ($request->project_note) {
            $p_contact = ProjectContact::where('contact_id', $contact->id)->where('project_id', $request->project_id)->first();
            $p_contact->note = $request->project_note;
            $p_contact->save();
        }

        DB::commit();

        return redirect()->back()->with('contacts', 'Контакт изменен');
    }

    public function contact_delete(Request $request)
    {
        DB::beginTransaction();

        $contact = ContractorContact::findOrFail($request->contact_id);

        $contact->delete();

        DB::commit();

        Session::flash('contacts', 'Контакт удален');

        return \GuzzleHttp\json_encode(true);
    }

    public function is_unique(Request $request)
    {
        if ($request->full_name) {
            if (Contractor::where('full_name', $request->full_name)->where('id', '!=', $request->id)->first()) {
                return \GuzzleHttp\json_encode(['full_name', 'Полное наименование']);
            }
        }
        if ($request->inn) {
            if (Contractor::where('inn', $request->inn)->where('id', '!=', $request->id)->first()) {
                return \GuzzleHttp\json_encode(['inn', 'ИНН']);
            }
        }
        if ($request->ogrn) {
            if (Contractor::where('ogrn', $request->ogrn)->where('id', '!=', $request->id)->first()) {
                return \GuzzleHttp\json_encode(['ogrn', 'ОГРН']);
            }
        }
        if ($request->email) {
            if (Contractor::where('email', $request->email)->where('id', '!=', $request->id)->first()) {
                return \GuzzleHttp\json_encode(['email', 'email']);
            }
        }

        return \GuzzleHttp\json_encode(true);
    }

    public function tasks($id): View
    {
        $contractor = Contractor::findOrFail($id);

        if ($contractor->in_archive) {
            abort(403);
        }

        $contacts = ContractorContact::where('contractor_id', $id)->get();

        $solved_tasks = Task::orderBy('created_at', 'desc')
            ->with('responsible_user', 'author', 'user')
            ->where('tasks.contractor_id', $id)
            ->leftJoin('users', 'users.id', '=', 'tasks.user_id')
            ->leftjoin('projects', 'projects.id', 'tasks.project_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->leftjoin('project_objects', 'project_objects.id', 'projects.object_id')
            ->select('projects.name as project_name', 'contractors.short_name as contractor_name',
                'project_objects.address as object_address', 'tasks.*');

        $task_files = TaskFile::whereIn('task_files.task_id', $solved_tasks->pluck('tasks.id'))
            ->leftJoin('users', 'users.id', '=', 'task_files.user_id')
            ->select('task_files.*');

        $task_redirects = TaskRedirect::select('task_redirects.*', 'tasks.project_id')
            ->leftJoin('tasks', 'tasks.id', 'task_redirects.task_id')
            ->where('tasks.project_id', $id);

        $task_responsible_users = User::select('users.id', 'users.first_name', 'users.last_name')
            ->whereIn('users.id', $solved_tasks->pluck('responsible_user_id'))
            ->orWhereIn('users.id', $task_redirects->pluck('old_user_id'))
            ->orWhereIn('users.id', $task_redirects->pluck('responsible_user_id'))
            ->orWhereIn('users.id', $solved_tasks->pluck('description'));

        $com_offers = CommercialOffer::whereIn('project_id', $solved_tasks->pluck('project_id'))->get();

        $work_volumes = WorkVolume::where('project_id', $id)
            ->orderBy('work_volumes.version', 'desc')
            ->with('get_requests');

        $solved_tasks = $solved_tasks->get();

        return view('contractors.tasks', [
            'solved_tasks' => $solved_tasks,
            'task_files' => $task_files->get(),
            'task_responsible_users' => $task_responsible_users->get(),
            'task_redirects' => $task_redirects->get(),
            'contractor' => $contractor,
            'contacts' => $contacts,
            'com_offers' => $com_offers,
            'work_volumes' => $work_volumes->get(),
        ]);
    }

    public function contractor_delete_request(Request $request)
    {
        DB::beginTransaction();

        // check remove task existence
        if (Task::where('status', 19)->where('target_id', $request->contractor_id)->where('is_solved', 0)->first()) {
            return back();
        }

        // create task
        $task = Task::create([
            'name' => 'Контроль удаления контрагента '.$request->contractor_name,
            'description' => 'Пользователь '.Auth::user()->full_name.
                ' отправил заявку на удаление контрагента с комментарием: '.$request->reason.
                '. Необходимо подтвердить или отклонить удаление контрагента',
            'responsible_user_id' => Group::find(5/*3*/)->getUsers()->first()->id,
            'user_id' => Auth::id(),
            'contractor_id' => $request->contractor_id,
            'target_id' => $request->contractor_id,
            'expired_at' => $this->addHours(48),
            'status' => 19,
        ]);

        DB::commit();

        ContractorDeletionControlTaskNotice::send(
            $task->responsible_user_id,
            [
                'name' => 'Новая задача «'.$task->name.'»',
                'additional_info' => ' Ссылка на задачу: ',
                'url' => $task->task_route(),
                'task_id' => $task->id,
                'contractor_id' => $task->contractor_id,
            ]
        );

        return back();
    }

    public function remove_task($id): View
    {

        $task = Task::where('tasks.id', $id)
            ->leftJoin('users', 'users.id', '=', 'tasks.user_id')
            ->leftjoin('contractors', 'contractors.id', 'tasks.contractor_id')
            ->select(DB::raw('CONCAT(users.last_name, " ", users.first_name, " ", users.patronymic) AS full_name'),
                'contractors.short_name as contractor_name', 'tasks.*')
            ->firstOrfail()->load('author');

        $contractor = Contractor::find($task->target_id);

        $task->update(['is_seen' => 1]);

        $target = route('contractors::card', $task->target_id);

        return view('tasks.remove_task', [
            'task' => $task,
            'target' => $target,
            'contractor' => $contractor,
        ]);
    }

    public function solve_remove(Request $request, $id)
    {
        DB::beginTransaction();

        $task = Task::findOrFail($id);
        $contractor = Contractor::findOrFail($task->contractor_id)->load('projects');

        $task->result = $request->status_result == 'accept' ? 1 : 2;
        $task->final_note = $task->descriptions[$task->status].$task->results[$task->status][$task->result].
            ($request->description ? ', с комментарием: '.$request->description : '');
        $task->solve_n_notify();

        if ($request->status_result == 'accept') {
            if (Schema::hasColumn($contractor->getTable(), 'deleted_at')) {
                foreach ($contractor->projects as $project) {
                    $project->all_tasks()->delete();
                    $project->contracts()->delete();
                    $project->com_offers()->delete();
                    $project->wvs()->delete();
                    $project->delete();
                }
                $contractor->project_relations()->delete();
                $contractor->delete();
            } else {
                return back()->with('no_soft', 'Возникли проблемы с удалением, пожалуйста, свяжитесь с разработчиками');
            }
        }

        DB::commit();

        ContractorDeletionControlTaskResolutionNotice::send(
            $task->user_id,
            [
                'name' => 'Запрашиваемый вами контрагент '.$contractor->short_name.' '.$task->results[$task->status][$task->result].
                    ($request->description ? ', с комментарием: '.$request->description : ''),
                'task_id' => $task->id,
                'contractor_id' => $task->contractor_id,
            ]
        );

        return redirect()->route('tasks::index');
    }

    public function get_by_type()
    {
        $contractors = Contractor::byType(request('contractor_type'));

        if (request('q')) {
            $contractors = $contractors->where('full_name', 'like', '%'.trim(request('q')).'%')
                ->orWhere('short_name', 'like', '%'.trim(request('q')).'%')
                ->orWhere('inn', 'like', '%'.trim(request('q')).'%')
                ->orWhere('kpp', 'like', '%'.trim(request('q')).'%');
        }

        $contractors = $contractors->where('in_archive', 0)->take(10)->get();
        $results = [];

        foreach ($contractors as $contractor) {
            $results[] = ['id' => $contractor->id.'', 'text' => $contractor->short_name];
        }

        return ['results' => $results];
    }

    public function getContractors(Request $request)
    {
        $contractors = Contractor::where('in_archive', 0);

        if ($request->q) {
            $contractors->whereRaw("REPLACE(full_name, '\"', '') LIKE '%{$request->q}%'")
                ->orWhereRaw("REPLACE(short_name, '\"', '') LIKE '%{$request->q}%'")
                ->orWhere('inn', 'like', '%'.$request->q.'%')
                ->orWhere('kpp', 'like', '%'.$request->q.'%')
                ->orWhere('legal_address', 'like', '%'.$request->q.'%');
        }

        return $contractors->limit(20)->get()->map(function ($contractor) {
            return ['code' => $contractor->id.'', 'label' => $contractor->short_name];
        });
    }

    public function solveTaskCheckContractor(Request $request, $task_id): RedirectResponse
    {
        $task = Task::findOrFail($task_id);
        $task->load('changing_fields', 'contractor');

        $contractor = $task->contractor;

        if ($request->change_fields) {
            foreach ($task->changing_fields as $field) {
                $name = $field->field_name;
                $contractor->$name = $field->value;
            }
            $contractor->save();
        }

        $solvedTasks = Task::whereStatus($task->status)->whereContractorId($contractor->id)->get();
        foreach ($solvedTasks as $solvedTask) {
            if ($request->change_fields) {
                $solvedTask->result = 1;
            } else {
                $solvedTask->result = 2;
            }

            $solvedTask->solve_n_notify();
        }

        return redirect()->back();
    }
}
